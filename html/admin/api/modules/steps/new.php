<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("TRAINING:EDIT")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['modulesSteps_name']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$array['modulesSteps_show'] = 0;

$DBLIB->where("modules.modules_deleted", 0);
$DBLIB->where("modules.modules_id", $array['modules_id']);
$DBLIB->where("modules.instances_id", $AUTH->data['instance']['instances_id']);
$module = $DBLIB->getOne('modules', ["modules.modules_id"]);
if (!$module) finish(false, ["message" => "Can't find module"]);

$insert = $DBLIB->insert("modulesSteps", $array);
if (!$insert) finish(false, ["message" => "Can't insert module step"]);

$bCMS->auditLog("INSERT", "modulesSteps", json_encode($array), $AUTH->data['users_userid']);
finish(true);