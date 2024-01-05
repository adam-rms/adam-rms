<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("FILES:FILE_ATTACHMENTS:EDIT:SHARING_SETTINGS") or !isset($_POST['s3files_id'])) die("404");


$shareKey = $bCMS->randomString(40);

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("s3files_id", $_POST['s3files_id']);
$update = $DBLIB->update("s3files", ["s3files_shareKey" => $shareKey],1); //Add a share key
if (!$update) finish(false);

$bCMS->auditLog("SHARE-FILE", "s3files", null, $AUTH->data['users_userid'],null, $_POST['s3files_id']);
finish(true,null,["s3files_shareKey" => hash('sha256', $shareKey . "|" . $_POST['s3files_id'])]);