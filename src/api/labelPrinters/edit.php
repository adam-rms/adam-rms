<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:LABEL_PRINTERS:EDIT")) {
    finish(false, ["code" => "AUTH"]);
}

if (!isset($_POST['labelPrinters_id'])) {
    finish(false, ["code" => "MISSING-ID"]);
}

// Verify ownership
$DBLIB->where("labelPrinters_id", $_POST['labelPrinters_id']);
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$printer = $DBLIB->getOne("labelPrinters");

if (!$printer) {
    finish(false, ["code" => "NOT-FOUND"]);
}

$data = [];
if (isset($_POST['name'])) $data['labelPrinters_name'] = $_POST['name'];
if (isset($_POST['manufacturer'])) $data['labelPrinters_manufacturer'] = $_POST['manufacturer'];
if (isset($_POST['model'])) $data['labelPrinters_model'] = $_POST['model'];
if (isset($_POST['cupsName'])) $data['labelPrinters_cupsName'] = $_POST['cupsName'];
if (isset($_POST['cupsServer'])) $data['labelPrinters_cupsServer'] = $_POST['cupsServer'];
if (isset($_POST['capabilities'])) $data['labelPrinters_capabilities'] = json_encode($_POST['capabilities']);

$DBLIB->where("labelPrinters_id", $_POST['labelPrinters_id']);
if (!$DBLIB->update("labelPrinters", $data)) {
    finish(false, ["code" => "UPDATE-FAIL"]);
}

$bCMS->auditLog(
    "EDIT-LABELPRINTER",
    "labelPrinters",
    json_encode($data),
    $AUTH->data['users_userid'],
    null,
    $AUTH->data['instance']['instances_id'],
    $_POST['labelPrinters_id']
);

finish(true);