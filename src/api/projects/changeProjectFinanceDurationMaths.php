<?php
require_once __DIR__ . '/../apiHeadSecure.php';
require_once __DIR__ . '/../../common/libs/bCMS/projectFinance.php';
use Money\Currency;
use Money\Money;

if (
    !$AUTH->instancePermissionCheck("PROJECTS:EDIT:DATES") or !isset($_POST['projects_id']) or
    !isset($_POST['projects_dates_finances_days']) or !isset($_POST['projects_dates_finances_weeks'])
) die("404");

$DAYS = intval($_POST['projects_dates_finances_days']);
$WEEKS = intval($_POST['projects_dates_finances_weeks']);
$REMOVECUSTOM = $_POST['projects_dates_finances_days'] == -1 and $_POST['projects_dates_finances_weeks'] == -1;

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects_id", "projects_dates_deliver_start", "projects_dates_deliver_end", "projects_dates_finances_days", "projects_dates_finances_weeks"]);
if (!$project) finish(false);

$projectFinanceHelper = new projectFinance();
$projectFinanceCacher = new projectFinanceCacher($project['projects_id']);
$priceMathsOld = $projectFinanceHelper->durationMaths($project['projects_id']);
if ($REMOVECUSTOM) $priceMathsNew = $projectFinanceHelper->durationMathsByDates($project['projects_dates_deliver_start'], $project['projects_dates_deliver_end']);
else $priceMathsNew = ["days" => $DAYS, "weeks" => $WEEKS];

//We're changing dates so we need to update pricing
$DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
$DBLIB->where("assetsAssignments.projects_id", $project['projects_id']);
$DBLIB->join("assets", "assetsAssignments.assets_id=assets.assets_id", "LEFT");
$DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$assets = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.assets_id", "assetsAssignments.assetsAssignments_id", "assetsAssignments_customPrice", "assetsAssignments_discount", "assetTypes_weekRate", "assetTypes_dayRate", "assets_dayRate", "assets_weekRate"]);
if ($assets) {
    foreach ($assets as $asset) {
        if ($asset['assetsAssignments_customPrice'] != null) continue; //There is a custom price set - so this asset is date agnostic anyway

        $priceOriginal = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
        $priceOriginal = $priceOriginal->add((new Money(($asset['assets_dayRate'] !== null ? $asset['assets_dayRate'] : $asset['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMathsOld['days']));
        $priceOriginal = $priceOriginal->add((new Money(($asset['assets_weekRate'] !== null ? $asset['assets_weekRate'] : $asset['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMathsOld['weeks']));
        $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $priceOriginal, true);

        $price = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
        $price = $price->add((new Money(($asset['assets_dayRate'] !== null ? $asset['assets_dayRate'] : $asset['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMathsNew['days']));
        $price = $price->add((new Money(($asset['assets_weekRate'] !== null ? $asset['assets_weekRate'] : $asset['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMathsNew['weeks']));
        $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $price, false);

        if ($asset['assetsAssignments_discount'] > 0) {
            //Remove old discount
            $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $priceOriginal->subtract($priceOriginal->multiply(1 - ($asset['assetsAssignments_discount'] / 100))), true);
            //Set a new discount
            $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $price->subtract($price->multiply(1 - ($asset['assetsAssignments_discount'] / 100))), false);
        }
    }
}

if ($projectFinanceCacher->save()) {
    $DBLIB->where("projects.projects_id", $project['projects_id']);
    $projectUpdate = $DBLIB->update("projects", [
        "projects_dates_finances_days" => $REMOVECUSTOM ? null : $DAYS,
        "projects_dates_finances_weeks" => $REMOVECUSTOM ? null : $WEEKS
    ]);
    if (!$projectUpdate) finish(false);
    if ($REMOVECUSTOM) $bCMS->auditLog("CHANGE-DATE-FINANCE", "projects", "Removed custom days & weeks for asset cost calculation", $AUTH->data['users_userid'], null, $_POST['projects_id']);
    else $bCMS->auditLog("CHANGE-DATE-FINANCE", "projects", "Set custom days & weeks for asset cost calculation to " . $DAYS . " day(s) & " . $WEEKS . " week(s)", $AUTH->data['users_userid'], null, $_POST['projects_id']);
    finish(true, null, ["changed" => true]);
} else finish(false, ["message" => "Cannot modify finances to change dates"]);

/** @OA\Post(
 *     path="/projects/changeProjectFinanceDateMaths.php", 
 *     summary="Change Project Number of Days & Weeks for Finance", 
 *     description="Change the number of days and weeks for a project  
Requires Instance Permission PROJECTS:EDIT:DATES
", 
 *     operationId="changeProjectFinanceDateMaths", 
 *     tags={"projects"}, 
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
 *     @OA\Response(
 *         response="404", 
 *         description="Permission Error",
 *     ), 
 *     @OA\Parameter(
 *         name="projects_id",
 *         in="query",
 *         description="Project ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="projects_dates_finances_days",
 *         in="query",
 *         description="Number of days (set both this and weeks to -1 to remove custom)",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="projects_dates_finances_weeks",
 *         in="query",
 *         description="Number of weeks (set both this and weeks to -1 to remove custom)",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
