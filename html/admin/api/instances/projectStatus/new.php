<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_STATUSES:CREATE")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['projectsStatuses_name']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$array['instances_id'] = $AUTH->data['instance']['instances_id'];
$status = $DBLIB->insert("projectsstatuses", $array);
if (!$status) finish(false);

$bCMS->auditLog("INSERT", "projectsstatuses", json_encode($array), $AUTH->data['users_userid']);
finish(true);