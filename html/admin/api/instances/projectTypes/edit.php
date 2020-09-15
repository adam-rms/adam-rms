<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (!is_numeric($array['projectsTypes_id'])) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
elseif (!$AUTH->instancePermissionCheck(107)) die("404");

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projectsTypes_deleted", 0);
$DBLIB->where("projectsTypes_id", $array['projectsTypes_id']);
$category = $DBLIB->update("projectsTypes", $array);
if (!$category) finish(false);

$bCMS->auditLog("EDIT", "projectsTypes", json_encode($array), $AUTH->data['users_userid']);
finish(true);