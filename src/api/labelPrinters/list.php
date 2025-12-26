<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:LABEL_PRINTERS:VIEW")) {
    finish(false, ["code" => "AUTH", "message" => "No permission"]);
}

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("labelPrinters_deleted", 0);
$printers = $DBLIB->get("labelPrinters");

finish(true, null, $printers);