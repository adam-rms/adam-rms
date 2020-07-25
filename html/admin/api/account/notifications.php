<?php
	require_once __DIR__ . '/../apiHeadSecure.php';
	if ($_POST['users_userid'] != $AUTH->data["users_userid"] && $AUTH->permissionCheck(22)) $userid = $bCMS->sanitizeString($_POST['users_userid']);
	else $userid = $AUTH->data["users_userid"];
	$DBLIB->where("users_userid", $userid);
	if ($DBLIB->update ('users', ["users_notificationSettings" => json_encode($_POST['settings'])])) {
		$bCMS->auditLog("UPDATE", "users", "CHANGE NOTIFICATION SETTINGS", $AUTH->data['users_userid'],$userid);
		finish(true);
	}
	else finish(false);
?>
