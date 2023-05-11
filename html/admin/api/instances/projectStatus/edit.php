<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_STATUSES:EDIT")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['name'] == "projectsStatuses_assetsReleased") {
        $array[$item['name']] = $item['value'] == "on" ? 1 : 0;
    } else {
        $array[$item['name']] = $item['value'];
    }
}
if (strlen($array['projectsStatuses_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projectsStatuses_deleted", 0);
$DBLIB->where("projectsStatuses_id", $array['projectsStatuses_id']);
$status = $DBLIB->update("projectsStatuses", $array);
if (!$status) finish(false);

$bCMS->auditLog("EDIT", "projectsStatuses", json_encode($array), $AUTH->data['users_userid']);
finish(true);