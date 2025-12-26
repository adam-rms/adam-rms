<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:LABEL_PRINTERS:DELETE")) {
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

// Soft delete
$DBLIB->where("labelPrinters_id", $_POST['labelPrinters_id']);
$DBLIB->update("labelPrinters", ['labelPrinters_deleted' => 1]);

$bCMS->auditLog(
    "DELETE-LABELPRINTER",
    "labelPrinters",
    null,
    $AUTH->data['users_userid'],
    null,
    $AUTH->data['instance']['instances_id'],
    $_POST['labelPrinters_id']
);

finish(true);