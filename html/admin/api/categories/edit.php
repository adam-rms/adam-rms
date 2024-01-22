<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:ASSET_CATEGORIES:EDIT")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['assetCategories_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assetCategories_deleted", 0);
$DBLIB->where("assetCategories_id", $array['assetCategories_id']);
$category = $DBLIB->update("assetCategories", $array);
if (!$category) finish(false);

$bCMS->auditLog("EDIT", "assetCategories", json_encode($array), $AUTH->data['users_userid']);
finish(true);