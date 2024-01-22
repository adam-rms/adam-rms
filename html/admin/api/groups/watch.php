<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['assetGroups_id']) or !is_numeric($_POST['assetGroups_id'])) finish(false);

$current = $AUTH->data['users_assetGroupsWatching'];
$current = explode(",",$current);
if (in_array($_POST['assetGroups_id'],$current)) unset($current[array_search($_POST['assetGroups_id'],$current)]);
else array_push($current, $_POST['assetGroups_id']);

$current = implode(",",array_filter($current));

$DBLIB->where("users_userid", $AUTH->data['users_userid']);
if (!$DBLIB->update("users",["users_assetGroupsWatching" => $current],1)) finish(false);
$bCMS->auditLog("UPDATE", "users", "Set watching to " . $current, $AUTH->data['users_userid']);
finish(true);
