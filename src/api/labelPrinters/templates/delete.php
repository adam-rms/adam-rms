<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:LABEL_PRINTERS:DELETE")) {
    finish(false, ["code" => "AUTH"]);
}

if (!isset($_POST['labelTemplates_id'])) {
    finish(false, ["code" => "MISSING-ID"]);
}

// Verify ownership
$DBLIB->where("labelTemplates_id", $_POST['labelTemplates_id']);
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$template = $DBLIB->getOne("labelTemplates");

if (!$template) {
    finish(false, ["code" => "NOT-FOUND"]);
}

// Soft delete
$DBLIB->where("labelTemplates_id", $_POST['labelTemplates_id']);
$DBLIB->update("labelTemplates", ['labelTemplates_deleted' => 1]);

$bCMS->auditLog(
    "DELETE-LABELTEMPLATE",
    "labelTemplates",
    null,
    $AUTH->data['users_userid'],
    null,
    $AUTH->data['instance']['instances_id'],
    $_POST['labelTemplates_id']
);

finish(true);