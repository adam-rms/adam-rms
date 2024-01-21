<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if ($_POST['users_userid'] != $PAGEDATA['USERDATA']['users_userid'] && $AUTH->serverPermissionCheck("USERS:EDIT:THUMBNAIL"))	$userid = $bCMS->sanitizeString($_POST['users_userid']);
else $userid = $PAGEDATA['USERDATA']['users_userid'];

$DBLIB->where("users_userid", $userid);
if ($DBLIB->update ('users', ["users_thumbnail" => $bCMS->sanitizeString($_POST['thumbnail'])])) {
	$bCMS->auditLog("UPDATE", "users", "CHANGE THUMBNAIL", $AUTH->data['users_userid'],$userid);
	finish(true);
}
else finish(false);
?>
