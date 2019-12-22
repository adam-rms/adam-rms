<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(19)) die("Sorry - you can't access this page");

if (!isset($_POST['assets_id'])) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assets.assets_id", $_POST['assets_id']);
$DBLIB->where("assets.assets_deleted", 0);
$asset = $DBLIB->getone("assets", ['assets_id']);
if (!$asset) finish(false, ["code" => "DELETE-FAIL", "message"=> "Could not find asset"]);

$DBLIB->where("assets_id", $_POST['assets_id']);
$result = $DBLIB->update("assets", ["assets_deleted" => 1]);
if (!$result) finish(false, ["code" => "DELETE-FAIL", "message"=> "Could not delete asset"]);
else finish(true);