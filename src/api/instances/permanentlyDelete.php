<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->serverPermissionCheck("INSTANCES:PERMANENTLY_DELETE") or !isset($_POST['instances_id'])) die("404");

$DBLIB->where('instances_id', $_POST['instances_id']);
$DBLIB->where("instances_deleted", 1);
if (!$DBLIB->getOne("instances", "instances_id")) finish(false);

$DBLIB->where('instances_id', $_POST['instances_id']);
$DBLIB->where("instances_deleted", 1);
if($DBLIB->delete('instances')) {
    $bCMS->auditLog("DELETE-INSTANCE", "instances", "Delete ". $_POST['instances_id'], $AUTH->data['users_userid'],null, $_POST['instances_id']);
    finish(true);
} else finish(false);

