<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['term'])) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

$DBLIB->where("users.users_deleted", 0);
$DBLIB->where("users.users_suspended", 0);
$DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
$DBLIB->where("instancePositions.instances_id",  $AUTH->data['instance']['instances_id']);
$DBLIB->where("userInstances.userInstances_deleted",  0);
$DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
$DBLIB->orderBy("users.users_name1", "ASC");
$DBLIB->orderBy("users.users_name2", "ASC");
if (strlen($_POST['term']) > 0) {
    $DBLIB->where("(
		users_name1 LIKE '%" . $bCMS->sanitizeString($_POST['term']) . "%'
		OR users_name2 LIKE '%" . $bCMS->sanitizeString($_POST['term']) . "%'	
		OR CONCAT( users_name1,  ' ', users_name2 ) LIKE '%" . $bCMS->sanitizeString($_POST['term']) . "%'
    )");
}
$users = $DBLIB->get("users", 15, ["users.users_userid", "users.users_name1", "users.users_name2"]);
if (!$users) finish(false, ["code" => "LIST-USERS-FAIL", "message"=> "Could not search for Users"]);
else {
    finish(true, null, $users);
}
