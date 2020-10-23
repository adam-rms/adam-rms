<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(120) or !isset($_POST['userid']) or !isset($_POST['modules_id'])) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

$DBLIB->where("users_userid",$_POST['userid']);
$DBLIB->where("modules_id", $_POST['modules_id']);
$update = $DBLIB->update("userModulesCertifications",["userModulesCertifications_revoked" => 1]);
if (!$update) finish(false, ["message"=> "Could not edit certification"]);
else {
    $bCMS->auditLog("REVOKE-ALL-CERTS", "userModulesCertifications", $_POST['modules_id'], $AUTH->data['users_userid'],$_POST['userid']);
    finish(true);
}