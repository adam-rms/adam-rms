<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(16) or !isset($_POST['name'])) die("404");

$instance = $DBLIB->insert("instancePositions", [
    "instances_id" => $AUTH->data['instance']['instances_id'],
    "instancePositions_displayName" => $_POST['name'],
]);
if ($instance) finish(true);
else finish(false, ["code" => "CREATE-INSTANCE-POSITION-FAIL", "message"=> "Could not create new instance position"]);
