<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:LABEL_PRINTERS:CREATE")) {
    finish(false, ["code" => "AUTH"]);
}

if (!isset($_POST['name']) || !isset($_POST['width']) || !isset($_POST['template'])) {
    finish(false, ["code" => "MISSING-FIELDS"]);
}

$data = [
    'instances_id' => $AUTH->data['instance']['instances_id'],
    'labelTemplates_name' => $_POST['name'],
    'labelTemplates_description' => $_POST['description'] ?? null,
    'labelTemplates_manufacturer' => $_POST['manufacturer'] ?? 'brother',
    'labelTemplates_width' => (int)$_POST['width'],
    'labelTemplates_height' => (int)($_POST['height'] ?? 0),
    'labelTemplates_orientation' => $_POST['orientation'] ?? 'horizontal',
    'labelTemplates_template' => json_encode($_POST['template'])
];

$id = $DBLIB->insert("labelTemplates", $data);
if (!$id) {
    finish(false, ["code" => "INSERT-FAIL"]);
}

$bCMS->auditLog(
    "CREATE-LABELTEMPLATE",
    "labelTemplates",
    json_encode($data),
    $AUTH->data['users_userid'],
    null,
    $AUTH->data['instance']['instances_id'],
    $id
);

finish(true, null, ['id' => $id]);