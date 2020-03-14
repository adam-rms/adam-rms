<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(56) or !isset($_POST['s3files_id'])) die("404");

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("s3files_id", $_POST['s3files_id']);
$update = $DBLIB->update("s3files", ["s3files_name" => $_POST['s3files_name']]);
if (!$update) finish(false);

$bCMS->auditLog("RENAME-FILE", "s3files", "Set the name to ". $_POST['s3files_name'], $AUTH->data['users_userid'],null, $_POST['s3files_id']);
finish(true);