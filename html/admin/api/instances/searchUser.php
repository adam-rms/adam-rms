<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(3) or !isset($_POST['term'])) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);
//Duplicated for adding crew
$DBLIB->where("users.users_deleted", 0);
$DBLIB->where("users.users_suspended", 0);
$DBLIB->where("(
    SELECT COUNT(*) FROM userInstances
    LEFT JOIN instancePositions ON userInstances.instancePositions_id=instancePositions.instancePositions_id
    WHERE userInstances.users_userid=users.users_userid AND (userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')
    AND userInstances.userInstances_deleted = '0'
    AND instancePositions.instances_id = '" . $AUTH->data['instance']['instances_id'] . "' 
    ) < 1");
/*$DBLIB->where("(
		users_email LIKE '%" . $bCMS->sanitizeStringMYSQL($_POST['term']) . "%'
		OR users_name1 LIKE '%" . $bCMS->sanitizeStringMYSQL($_POST['term']) . "%'
		OR users_name2 LIKE '%" . $bCMS->sanitizeString($_POST['term']) . "%'	
		OR CONCAT( users_name1,  ' ', users_name2 ) LIKE '%" . $bCMS->sanitizeString($_POST['term']) . "%'
    )");*/
$DBLIB->where("users_email", strtolower($bCMS->sanitizeString($_POST['term']))); //Only allow searching by email
$users = $DBLIB->get("users", null, ["users_userid", "users_name1", "users_name2"]);
if (!$users) finish(true, null, []);
else finish(true, null, $users);