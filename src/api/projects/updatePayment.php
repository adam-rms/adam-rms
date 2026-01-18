<?php
require_once __DIR__ . '/../apiHeadSecure.php';
require_once __DIR__ . '/../../common/libs/bCMS/projectFinance.php';

use Money\Currency;
use Money\Money;
use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_PAYMENTS:CREATE")) die("404");

// Normalise incoming form data
$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}

if (empty($array['payments_id']) || empty($array['projects_id'])) finish(false, ["code" => "PARAM-ERROR", "message" => "Missing identifiers"]);

// Fetch existing payment to compute deltas
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->join("projects", "payments.projects_id=projects.projects_id", "LEFT");
$DBLIB->where("payments.payments_id", $array['payments_id']);
$existing = $DBLIB->getone("payments", ["payments.payments_id", "payments.projects_id", "payments.payments_type", "payments.payments_amount", "payments.payments_quantity"]);
if (!$existing) finish(false, ["code" => "NOT-FOUND", "message" => "Payment not found"]);

$currencies = new ISOCurrencies();
$moneyParser = new DecimalMoneyParser($currencies);

// Prepare new values
$array['payments_date'] = isset($array['payments_date']) ? date("Y-m-d H:i:s", strtotime($array['payments_date'])) : null;
if (!isset($array['payments_quantity']) || !$array['payments_quantity']) {
    $array['payments_quantity'] = 1;
}
try {
    $array['payments_amount'] = $moneyParser->parse(
        $array['payments_amount'],
        $AUTH->data['instance']['instances_config_currency']
    )->getAmount();
} catch (\Throwable $e) {
    finish(false, ["code" => "PARAM-ERROR", "message" => "Invalid amount format"]);
}
$array['payments_type'] = intval($array['payments_type']);

$projectFinanceCacher = new projectFinanceCacher($existing['projects_id']);

// Remove identifiers from update data - they shouldn't be changed
unset($array['payments_id']);
unset($array['projects_id']);

// Apply update
$DBLIB->where("payments_id", $existing['payments_id']);
$update = $DBLIB->update("payments", $array, 1);
if (!$update) finish(false);

// Adjust finance cache using delta
$oldAmount = new Money($existing['payments_amount'], new Currency($AUTH->data['instance']['instances_config_currency']));
$oldAmount = $oldAmount->multiply($existing['payments_quantity']);
$projectFinanceCacher->adjustPayment($existing['payments_type'], $oldAmount, true);

$newAmount = new Money($array['payments_amount'], new Currency($AUTH->data['instance']['instances_config_currency']));
$newAmount = $newAmount->multiply($array['payments_quantity']);
$projectFinanceCacher->adjustPayment($array['payments_type'], $newAmount, false);

$bCMS->auditLog("UPDATE", "payments", $array['payments_id'], $AUTH->data['users_userid'], null, $existing['projects_id']);

if ($projectFinanceCacher->save()) finish(true);
else finish(false, ["message" => "Finance Cacher Save failed"]);

/** @OA\Post(
 *     path="/projects/updatePayment.php",
 *     summary="Update Payment",
 *     description="Edit an existing project payment. Requires Instance Permission PROJECTS:PROJECT_PAYMENTS:CREATE",
 *     operationId="updatePayment",
 *     tags={"projects"},
 *     @OA\Response(
 *         response="200",
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(type="object", @OA\Property(property="result", type="boolean"))
 *         )
 *     ),
 *     @OA\Response(response="404", description="Permission Error"),
 *     @OA\Parameter(name="formData", in="query", description="Serialized form data", required="true", @OA\Schema(type="object"))
 * )
 */
