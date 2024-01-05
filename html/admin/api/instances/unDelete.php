<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->serverPermissionCheck("INSTANCES:DELETE") or !isset($_POST['instances_id'])) die("404");

$DBLIB->where('instances_id', $_POST['instances_id']);
$DBLIB->where("instances_deleted", 1);
if($DBLIB->update('instances', ["instances_deleted" => 0], 1)) {
    $bCMS->auditLog("RESTORE-DELETED-INSTANCE", "instances", "Un-delete ". $_POST['instances_id'], $AUTH->data['users_userid'],null, $_POST['instances_id']);
    finish(true);
} else finish(false);

