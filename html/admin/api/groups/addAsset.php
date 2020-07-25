<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(96)) die("404");
if (!isset($_POST['assetGroups_id']) or !is_numeric($_POST['assetGroups_id']) or !is_numeric($_POST['assetGroups_id'])) finish(false);

$DBLIB->where("assets_id",$_POST['assets_id']);
$DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$asset = $DBLIB->getone("assets", ["assets_assetGroups","assets_tag","assetTypes_name"]);
if (!$asset) finish(false);

$DBLIB->where("assetGroups_id", $_POST['assetGroups_id']);
$DBLIB->where("(users_userid IS NULL OR users_userid = '" . $AUTH->data['users_userid'] . "')");
$DBLIB->where("instances_id",$AUTH->data['instance']["instances_id"]);
$DBLIB->where("assetGroups_deleted",0);
$group = $DBLIB->getone('assetGroups',["assetGroups_name","assetGroups_id"]);
if (!$group) finish(false);

$current = $asset['assets_assetGroups'];
$current = explode(",", $current);
if (!in_array($_POST['assetGroups_id'], $current)) array_push($current, $_POST['assetGroups_id']);
$current = implode(",", array_filter($current));

$DBLIB->where("assets_id",$_POST['assets_id']);
if (!$DBLIB->update("assets", ["assets_assetGroups" => $current], 1)) finish(false);

$bCMS->auditLog("UPDATE", "assets", "Add asset " . $_POST['assets_id'] . " to group " . $_POST['assetGroups_id'], $AUTH->data['users_userid']);


foreach ($bCMS->usersWatchingGroup($_POST['assetGroups_id']) as $user) {
    if ($user != $AUTH->data['users_userid']) notify(16,$user, $AUTH->data['instance']['instances_id'], "Asset " . $bCMS->aTag($asset['assets_tag']) . " added to group " . $group['assetGroups_name'], "Asset " . $bCMS->aTag($asset['assets_tag']) . " (" . $asset["assetTypes_name"] . ") has been added to the group " . $group['assetGroups_name'] . " by " . $AUTH->data['users_name1'] . " " . $AUTH->data['users_name2']);
}

finish(true);