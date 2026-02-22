<?php
require_once __DIR__ . '/../apiHeadSecure.php';
require_once __DIR__ . '/../../common/libs/bCMS/projectFinance.php';

use Money\Currency;
use Money\Money;
use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_PAYMENTS:CREATE")) die("404");

// Enforce POST-only semantics to avoid state changes via GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    finish(false, ["code" => "METHOD-NOT-ALLOWED", "message" => "POST required"]);
}

// Basic CSRF protection using a session-bound token
$sessionCsrfToken = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : null;
$requestCsrfToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null;

// If not provided as a top-level field, look for csrf_token inside formData
if ($requestCsrfToken === null && isset($_POST['formData']) && is_array($_POST['formData'])) {
    foreach ($_POST['formData'] as $item) {
        if (isset($item['name'], $item['value']) && $item['name'] === 'csrf_token') {
            $requestCsrfToken = $item['value'];
            break;
        }
    }
}

if (empty($sessionCsrfToken) || empty($requestCsrfToken) || !hash_equals((string) $sessionCsrfToken, (string) $requestCsrfToken)) {
    finish(false, ["code" => "CSRF-ERROR", "message" => "Invalid CSRF token"]);
}

// Normalise incoming form data
if (!isset($_POST['formData']) || !is_array($_POST['formData'])) {
    finish(false, ["code" => "PARAM-ERROR", "message" => "Invalid form data"]);
}

$array = [];
foreach ($_POST['formData'] as $item) {
    if (!isset($item['name'])) {
        continue;
    }
    // Skip CSRF token entry if present in formData
    if ($item['name'] === 'csrf_token') {
        continue;
    }
    $array[$item['name']] = isset($item['value']) ? $item['value'] : null;
}

if (empty($array['payments_id']) || empty($array['projects_id'])) finish(false, ["code" => "PARAM-ERROR", "message" => "Missing identifiers"]);

// Fetch existing payment to compute deltas
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("payments.payments_deleted", 0);
$DBLIB->join("projects", "payments.projects_id=projects.projects_id", "LEFT");
$DBLIB->where("payments.payments_id", $array['payments_id']);
$existing = $DBLIB->getone("payments", ["payments.payments_id", "payments.projects_id", "payments.payments_type", "payments.payments_amount", "payments.payments_quantity"]);
if (!$existing) finish(false, ["code" => "NOT-FOUND", "message" => "Payment not found"]);

// Ensure the supplied projects_id matches the project of the payment
if ((int) $array['projects_id'] !== (int) $existing['projects_id']) {
    finish(false, ["code" => "PROJECT-MISMATCH", "message" => "Payment does not belong to the specified project"]);
}
$currencies = new ISOCurrencies();
$moneyParser = new DecimalMoneyParser($currencies);

// Prepare new values
$array['payments_date'] = isset($array['payments_date']) ? date("Y-m-d H:i:s", strtotime($array['payments_date'])) : null;
if (!isset($array['payments_quantity']) || !$array['payments_quantity']) {
$existing = $DBLIB->getone("payments", ["payments.payments_id", "payments.projects_id", "payments.payments_type", "payments.payments_amount", "payments.payments_quantity", "payments.payments_date"]);
}
if (!$existing) finish(false, ["code" => "NOT-FOUND", "message" => "Payment not found"]);

$currencies = new ISOCurrencies();
$moneyParser = new DecimalMoneyParser($currencies);

// Prepare new values
// Determine payment type, falling back to existing if not supplied
if (isset($array['payments_type']) && $array['payments_type'] !== '') {
    $array['payments_type'] = intval($array['payments_type']);
} else {
    $array['payments_type'] = (int) $existing['payments_type'];
}

// Validate and normalise payment date
if (isset($array['payments_date']) && $array['payments_date'] !== '') {
    $timestamp = strtotime($array['payments_date']);
    if ($timestamp === false) {
        finish(false, ["code" => "PARAM-ERROR", "message" => "Invalid payment date"]);
    }
    $array['payments_date'] = date("Y-m-d H:i:s", $timestamp);
} else {
    // If this payment type requires a date and there is no existing date, reject the update
    if ($array['payments_type'] === 1 && empty($existing['payments_date'])) {
        finish(false, ["code" => "PARAM-ERROR", "message" => "Payment date is required for received payments"]);
    }
    // Do not update payments_date if no new value is provided
    unset($array['payments_date']);
}

$array['payments_quantity'] = ($array['payments_quantity'] ?? 1) === '' ? 1 : $array['payments_quantity'];
$array['payments_amount'] = $moneyParser->parse($array['payments_amount'], $AUTH->data['instance']['instances_config_currency'])->getAmount();

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

$bCMS->auditLog("UPDATE", "payments", $existing['payments_id'], $AUTH->data['users_userid'], null, $existing['projects_id']);

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
 *     @OA\Parameter(
 *         name="formData",
 *         in="query",
 *         description="Serialized form data for updating a project payment",
 *         required=true,
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(
 *                 property="payments_id",
 *                 type="integer",
 *                 description="Identifier of the payment to update"
 *             ),
 *             @OA\Property(
 *                 property="projects_id",
 *                 type="integer",
 *                 description="Identifier of the project this payment belongs to"
 *             ),
 *             @OA\Property(
 *                 property="payments_date",
 *                 type="string",
 *                 format="date-time",
 *                 nullable=true,
 *                 description="Date and time of the payment"
 *             ),
 *             @OA\Property(
 *                 property="payments_quantity",
 *                 type="integer",
 *                 description="Quantity associated with the payment (defaults to 1 if empty)"
 *             ),
 *             @OA\Property(
 *                 property="payments_amount",
 *                 type="string",
 *                 description="Payment amount in the instance currency, as a decimal string"
 *             ),
 *             @OA\Property(
 *                 property="payments_type",
 *                 type="integer",
 *                 description="Payment type identifier"
 *             )
 *         )
 *     )
 * )
 */
