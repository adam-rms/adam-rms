<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(37) or !isset($_POST['clients_name'])) die("404");

$client = $DBLIB->insert("clients", [
    "clients_name" => $_POST['clients_name'],
    "instances_id" => $AUTH->data['instance']['instances_id'],
]);
if (!$client) finish(false, ["code" => "CREATE-CLIENT-FAIL", "message"=> "Could not create new client"]);

$bCMS->auditLog("INSERT", "clients",null, $AUTH->data['users_userid'],null, $client);
finish(true, null, ["clients_id" => $client]);