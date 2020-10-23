<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(115)) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['modules_name']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$array['instances_id'] = $AUTH->data['instance']['instances_id'];
$array['modules_id'] = null;
$array['users_userid'] = $AUTH->data['users_userid'];
$insert = $DBLIB->insert("modules", $array);
if (!$insert) finish(false, ["message" => $DBLIB->getLastError()]);

$insertStep = $DBLIB->insert("modulesSteps", [
    "modules_id" => $insert,
    "modulesSteps_show" => 1,
    "modulesSteps_name" => "Introduction",
    "modulesSteps_order" => 0,
    "modulesSteps_internalNotes" => "Use this step to introduce the learning objectives and the module, but don't use it to cover any content",
    "modulesSteps_content" => "Welcome to this module. The learning objectives are:<br/><br/>" . str_replace("\n","<br/>",$array['modules_learningObjectives']),
    "modulesSteps_type" => 1,
    "modulesSteps_locked" => 1
]);

$bCMS->auditLog("INSERT", "modules", json_encode($array), $AUTH->data['users_userid']);
finish(true);