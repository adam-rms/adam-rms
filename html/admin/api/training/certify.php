<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("TRAINING:EDIT:CERTIFY_USER") or !isset($_POST['userid'])) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

$insert = $DBLIB->insert("userModulesCertifications",[
    "users_userid" => $_POST['userid'],
    "userModulesCertifications_approvedBy" => $AUTH->data['users_userid'],
    "modules_id" => $_POST['modules_id'],
    "userModulesCertifications_approvedComment" => $_POST['comment'],
    "userModulesCertifications_timestamp" => date('Y-m-d H:i:s')
]);
if (!$insert) finish(false, ["message"=> "Could not add certification"]);
else finish(true);