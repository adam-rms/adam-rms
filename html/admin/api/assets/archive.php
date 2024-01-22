<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:ARCHIVE")) die("Sorry - you can't access this page");

if (!isset($_POST['assets_id'])) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assets.assets_id", $_POST['assets_id']);
$DBLIB->where("assets.assets_deleted", 0);
$DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
$asset = $DBLIB->getone("assets", ['assets_id']);
if (!$asset) finish(false, ["code" => "DELETE-FAIL", "message"=> "Could not find asset"]);

$update = [
    "assets_archived" => $_POST['reason'],
    "assets_endDate" => date("Y-m-d H:i:s", strtotime($_POST['date']))
];

$DBLIB->where("assets_id", $_POST['assets_id']);
$result = $DBLIB->update("assets", $update);
if (!$result) finish(false, ["code" => "ARCHIVE-FAIL", "message"=> "Could not archive asset"]);
else {
    $bCMS->auditLog("ARCHIVE", "assets", $asset['assets_id'], $AUTH->data['users_userid']);
    finish(true);
}