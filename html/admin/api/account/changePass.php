<?php
//Re-send a verification email
require_once __DIR__ . '/../apiHeadSecure.php';

if (hash($AUTH->data['users_hash'], $AUTH->data['users_salty1'] . $_POST['oldpass']. $AUTH->data['users_salty2']) != $AUTH->data['users_password']) finish(false,["message"=>"Current Password Incorrect"]);

$DBLIB->where ('users_userid', $AUTH->data['users_userid']);
if ($DBLIB->update ('users', ["users_password" => hash($CONFIG['nextHash'], $AUTH->data['users_salty1'] . $_POST['newpass']. $AUTH->data['users_salty2'])])) {
    $bCMS->auditLog("UPDATE", "users", "PASSWORD CHANGE", $AUTH->data['users_userid'],$AUTH->data['users_userid']);
    finish(true);
}
else finish(false);