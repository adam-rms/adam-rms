<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(88)) die("Sorry - you can't access this page");

if (!isset($_POST['barcodeid'])) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assets.assets_tag", $_POST['tag']);
$asset = $DBLIB->getone("assets",["assets_id"]);
if (!$asset) finish(false, ["message"=> "Asset not found"]);

$DBLIB->where("assetsBarcodes_id", $_POST['barcodeid']);
$DBLIB->where("(assets_id IS NULL)");
$result = $DBLIB->update("assetsBarcodes", ["assets_id" => $asset['assets_id']],1);
if (!$result) finish(false, ["code" => "UPDATE-FAIL", "message"=> "Could not update barcode"]);
else {
    $bCMS->auditLog("ASSOCIATE", "assetsBarcodes", $_POST['barcodeid'] . " set to " . $asset['assets_id'], $AUTH->data['users_userid'],null);
    finish(true);
}