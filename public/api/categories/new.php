<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(91)) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['assetCategories_name']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$array['instances_id'] = $AUTH->data['instance']['instances_id'];
$category = $DBLIB->insert("assetCategories", $array);
if (!$category) finish(false);

$bCMS->auditLog("INSERT", "assetCategories", json_encode($array), $AUTH->data['users_userid']);
finish(true);