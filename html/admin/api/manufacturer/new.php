<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(38) or !isset($_POST['manufacturers_name'])) die("404");

$insert = $DBLIB->insert("manufacturers", [
    "manufacturers_name" => $_POST['manufacturers_name'],
    "instances_id" => $AUTH->data['instance']['instances_id'],
]);
if (!$insert) finish(false, ["code" => "CREATE-CLIENT-FAIL", "message"=> "Could not create new manufacturers"]);

$bCMS->auditLog("INSERT", "manufacturers",null, $AUTH->data['users_userid'],null, $insert);
finish(true, null, ["manufacturers_id" => $insert]);