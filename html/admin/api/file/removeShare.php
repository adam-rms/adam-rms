<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("FILES:FILE_ATTACHMENTS:EDIT:SHARING_SETTINGS") or !isset($_POST['s3files_id'])) die("404");


$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("s3files_id", $_POST['s3files_id']);
$update = $DBLIB->update("s3files", ["s3files_shareKey" => null],1); //Remove a share key
if (!$update) finish(false);

$bCMS->auditLog("UNSHARE-FILE", "s3files", null, $AUTH->data['users_userid'],null, $_POST['s3files_id']);
finish(true);