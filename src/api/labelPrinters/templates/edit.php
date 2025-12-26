<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:LABEL_PRINTERS:EDIT")) {
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

$data = [];
if (isset($_POST['name'])) $data['labelTemplates_name'] = $_POST['name'];
if (isset($_POST['description'])) $data['labelTemplates_description'] = $_POST['description'];
if (isset($_POST['width'])) $data['labelTemplates_width'] = (int)$_POST['width'];
if (isset($_POST['height'])) $data['labelTemplates_height'] = (int)$_POST['height'];
if (isset($_POST['orientation'])) $data['labelTemplates_orientation'] = $_POST['orientation'];
if (isset($_POST['template'])) $data['labelTemplates_template'] = json_encode($_POST['template']);

$DBLIB->where("labelTemplates_id", $_POST['labelTemplates_id']);
if (!$DBLIB->update("labelTemplates", $data)) {
    finish(false, ["code" => "UPDATE-FAIL"]);
}

$bCMS->auditLog(
    "EDIT-LABELTEMPLATE",
    "labelTemplates",
    json_encode($data),
    $AUTH->data['users_userid'],
    null,
    $AUTH->data['instance']['instances_id'],
    $_POST['labelTemplates_id']
);

finish(true);