<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(116)) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['modulesSteps_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
if ($array['modulesSteps_show']) $array['modulesSteps_show'] = 1;
else $array['modulesSteps_show'] = 0;

$DBLIB->where("modules.modules_deleted", 0);
$DBLIB->where("modules.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->join("modules","modules.modules_id=modulesSteps.modules_id","LEFT");
$DBLIB->where("modulesSteps.modulesSteps_id",$array['modulesSteps_id']);
$id = $DBLIB->getOne("modulesSteps",["modulesSteps_id"]);
if (!$id) finish(false);

$DBLIB->where("modulesSteps.modulesSteps_id",$id['modulesSteps_id']);
$update = $DBLIB->update("modulesSteps", $array,1);
if (!$update) finish(false);

$bCMS->auditLog("UPDATE", "modulesSteps", json_encode($array), $AUTH->data['users_userid']);
finish(true);