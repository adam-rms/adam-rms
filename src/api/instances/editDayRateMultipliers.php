<?php
require_once __DIR__ . '/../apiHeadSecure.php';
require_once __DIR__ . '/../../common/libs/bCMS/projectFinance.php';
use Money\Currency;
use Money\Money;

if (!$AUTH->instancePermissionCheck("BUSINESS:BUSINESS_SETTINGS:EDIT")) finish(false, ["code" => "AUTH-ERROR", "message" => "No auth for action"]);
if (!isset($_POST['multipliers']) or !is_string($_POST['multipliers'])) finish(false, ["code" => "PARAM-ERROR", "message" => "No data for action"]);

// A list of {"from": days, "to": days, "multiplier": number of day rates to charge}, or an empty list to remove them all
$rules = json_decode($_POST['multipliers'], true);
if (!is_array($rules) or !array_is_list($rules)) finish(false, ["code" => "PARAM-ERROR", "message" => "Multipliers must be a list"]);
if (count($rules) > 100) finish(false, ["code" => "PARAM-ERROR", "message" => "Too many multipliers"]);
$clean = [];
foreach ($rules as $rule) {
    if (!is_array($rule) or !isset($rule['from'], $rule['to'], $rule['multiplier']) or !is_scalar($rule['multiplier'])) finish(false, ["code" => "PARAM-ERROR", "message" => "Each multiplier needs a from, to and multiplier"]);
    $from = filter_var($rule['from'], FILTER_VALIDATE_INT);
    $to = filter_var($rule['to'], FILTER_VALIDATE_INT);
    if ($from === false or $to === false or $from < 1 or $to > 9999 or $from > $to) finish(false, ["code" => "PARAM-ERROR", "message" => "Days must be whole numbers from 1, and \"from\" can't be after \"to\""]);
    $multiplier = trim((string) $rule['multiplier']);
    if (!preg_match('/^\d{1,5}(\.\d{1,2})?$/', $multiplier)) finish(false, ["code" => "PARAM-ERROR", "message" => "Multipliers must be a number of day rates with up to 2 decimal places"]);
    // Tidy it up, so "01.50" is stored as "1.5"
    $parts = explode('.', $multiplier . '.');
    $whole = ltrim($parts[0], '0') ?: '0';
    $fraction = rtrim($parts[1], '0');
    $multiplier = $fraction === '' ? $whole : $whole . '.' . $fraction;
    if ($whole === '0') finish(false, ["code" => "PARAM-ERROR", "message" => "Multipliers can't be less than 1 day rate - use a discount instead"]);
    $clean[] = ["from" => $from, "to" => $to, "multiplier" => $multiplier];
}
usort($clean, function ($a, $b) {
    return $a['from'] <=> $b['from'];
});
for ($i = 1; $i < count($clean); $i++) {
    if ($clean[$i]['from'] <= $clean[$i - 1]['to']) finish(false, ["code" => "PARAM-ERROR", "message" => "Multipliers can't cover the same number of days twice"]);
}

$currency = new Currency($AUTH->data['instance']['instances_config_currency']);
$projectFinanceHelper = new projectFinance();

// Projects priced by their dates (rather than a custom number of days and weeks) are repriced by the change, so their running totals need adjusting
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("(projects.projects_dates_finances_days IS NULL OR projects.projects_dates_finances_weeks IS NULL)");
$projects = $DBLIB->get("projects", null, ["projects_id"]);
$priceMathsOld = [];
foreach ($projects as $project) {
    $priceMathsOld[$project['projects_id']] = $projectFinanceHelper->durationMaths($project['projects_id']);
}

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$result = $DBLIB->update("instances", ["instances_dayRateMultipliers" => count($clean) > 0 ? json_encode($clean) : null]);
if (!$result) finish(false, ["code" => "UPDATE-FAIL", "message" => "Could not update multipliers"]);

foreach ($projects as $project) {
    $old = $priceMathsOld[$project['projects_id']];
    $new = $projectFinanceHelper->durationMaths($project['projects_id']);
    if ((string) $old['days'] === (string) $new['days'] and (string) $old['weeks'] === (string) $new['weeks']) continue;

    $projectFinanceCacher = new projectFinanceCacher($project['projects_id']);
    $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
    $DBLIB->where("assetsAssignments.projects_id", $project['projects_id']);
    $DBLIB->join("assets", "assetsAssignments.assets_id=assets.assets_id", "LEFT");
    $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->where("assets.assets_deleted", 0); //Deleting an asset takes it out of the totals but leaves it assigned, as projects/data.php expects
    $assets = $DBLIB->get("assetsAssignments", null, ["assetsAssignments_customPrice", "assetsAssignments_discount", "assetTypes_weekRate", "assetTypes_dayRate", "assets_dayRate", "assets_weekRate"]);
    foreach ($assets as $asset) {
        if ($asset['assetsAssignments_customPrice'] != null) continue; //There is a custom price set - so this asset is date agnostic anyway
        $dayRate = new Money(($asset['assets_dayRate'] !== null ? $asset['assets_dayRate'] : $asset['assetTypes_dayRate']), $currency);
        $weekRate = new Money(($asset['assets_weekRate'] !== null ? $asset['assets_weekRate'] : $asset['assetTypes_weekRate']), $currency);

        $priceOriginal = (new Money(null, $currency))->add($dayRate->multiply($old['days']))->add($weekRate->multiply($old['weeks']));
        $price = (new Money(null, $currency))->add($dayRate->multiply($new['days']))->add($weekRate->multiply($new['weeks']));
        $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $priceOriginal, true);
        $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $price, false);

        if ($asset['assetsAssignments_discount'] > 0) {
            $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $priceOriginal->subtract($priceOriginal->multiply(1 - ($asset['assetsAssignments_discount'] / 100))), true);
            $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $price->subtract($price->multiply(1 - ($asset['assetsAssignments_discount'] / 100))), false);
        }
    }
    $projectFinanceCacher->save();
}

$bCMS->auditLog("EDIT-INSTANCE", "instances", "Set day rate multipliers to " . json_encode($clean), $AUTH->data['users_userid'], null, null, $AUTH->data['instance']["instances_id"]);
finish(true, null, ["multipliers" => $clean]);

/** @OA\Post(
 *     path="/instances/editDayRateMultipliers.php",
 *     summary="Edit Day Rate Multipliers",
 *     description="Set how many day rates to charge for a hire lasting a given number of days (e.g. 1.5 day rates for 2 days). Projects priced by their dates are repriced.
Requires Instance Permission BUSINESS:BUSINESS_SETTINGS:EDIT
",
 *     operationId="editDayRateMultipliers",
 *     tags={"instances"},
 *     @OA\Response(
 *         response="200",
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="result",
 *                     type="boolean",
 *                     description="Whether the request was successful",
 *                 ),
 *             ),
 *         ),
 *     ),
 *     @OA\Parameter(
 *         name="multipliers",
 *         in="query",
 *         description="JSON list of {from: days, to: days, multiplier: day rates to charge}. An empty list removes them all.",
 *         required="true",
 *         @OA\Schema(
 *             type="string"),
 *         ),
 * )
 */
