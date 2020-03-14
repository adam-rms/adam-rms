<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(57) or !isset($_POST['s3files_id'])) die("404");

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("s3files_id", $_POST['s3files_id']);
$update = $DBLIB->update("s3files", ["s3files_meta_deleteOn" => date("Y-m-d H:i:s")]);
if (!$update) finish(false);

$bCMS->auditLog("DELETE-FILE", "s3files", null, $AUTH->data['users_userid'],null, $_POST['s3files_id']);
finish(true);