<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:LABEL_PRINTERS:VIEW")) {
    finish(false, ["code" => "AUTH"]);
}

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("labelTemplates_deleted", 0);
$templates = $DBLIB->get("labelTemplates");

finish(true, null, $templates);