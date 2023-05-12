<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_STATUSES:CREATE")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['name'] == "projectsStatuses_assetsReleased") {
        $array[$item['name']] = $item['value'] == "on" ? 1 : 0;
    } else {
        $array[$item['name']] = $item['value'];
    }
}
if (strlen($array['projectsStatuses_name']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$array['instances_id'] = $AUTH->data['instance']['instances_id'];
$status = $DBLIB->insert("projectsStatuses", $array);
if (!$status) finish(false);

$bCMS->auditLog("INSERT", "projectsStatuses", json_encode($array), $AUTH->data['users_userid']);
finish(true);