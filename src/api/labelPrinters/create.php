<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:LABEL_PRINTERS:CREATE")) {
    finish(false, ["code" => "AUTH", "message" => "No permission"]);
}

if (!isset($_POST['name']) || !isset($_POST['cupsName'])) {
    finish(false, ["code" => "MISSING-FIELDS"]);
}

$data = [
    'instances_id' => $AUTH->data['instance']['instances_id'],
    'labelPrinters_name' => $_POST['name'],
    'labelPrinters_manufacturer' => $_POST['manufacturer'] ?? 'brother',
    'labelPrinters_model' => $_POST['model'] ?? 'PT-P700',
    'labelPrinters_cupsName' => $_POST['cupsName'],
    'labelPrinters_cupsServer' => $_POST['cupsServer'] ?? null,
    'labelPrinters_capabilities' => json_encode($_POST['capabilities'] ?? [])
];

$id = $DBLIB->insert("labelPrinters", $data);
if (!$id) {
    finish(false, ["code" => "INSERT-FAIL"]);
}

$bCMS->auditLog(
    "CREATE-LABELPRINTER",
    "labelPrinters",
    json_encode($data),
    $AUTH->data['users_userid'],
    null,
    $AUTH->data['instance']['instances_id'],
    $id
);

finish(true, null, ['id' => $id]);