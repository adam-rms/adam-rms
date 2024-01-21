<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['term'])) finish(false, ["message"=> "No data for action"]);

$DBLIB->orderBy("users_userid", "DESC");
$DBLIB->orderBy("assetGroups_name", "ASC");
if (strlen($_POST['term']) > 0) {
    $DBLIB->where("(
        assetGroups_name LIKE '%" . $bCMS->sanitizeStringMYSQL($_POST['term']) . "%'
    )");
}
$DBLIB->where("(users_userid IS NULL OR users_userid = '" . $AUTH->data['users_userid'] . "')");
$DBLIB->where("instances_id",$AUTH->data['instance']["instances_id"]);
$DBLIB->where("assetGroups_deleted",0);
$groups = $DBLIB->get('assetGroups',15,["assetGroups_name","assetGroups_id"]);
if (!$groups) finish(false, ["message"=> "Could not search for Groups"]);
else finish(true, null, $groups);
