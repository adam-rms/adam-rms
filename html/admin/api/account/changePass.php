<?php
//Re-send a verification email
require_once __DIR__ . '/../apiHeadSecure.php';
header("Content-Type: text/plain");

if (hash($USERDATA['users_hash'], $USERDATA['users_salty1'] . $_GET['oldpass']. $USERDATA['users_salty2']) != $USERDATA['users_password']) die('2');

$DBLIB->where ('users_userid', $USERDATA['users_userid']);
if ($DBLIB->update ('users', ["users_password" => hash($CONFIG['nextHash'], $USERDATA['users_salty1'] . $_GET['newpass']. $USERDATA['users_salty2'])])) {
    $bCMS->auditLog("UPDATE", "users", "PASSWORD CHANGE", $AUTH->data['users_userid'],$AUTH->data['users_userid']);
    die('1');
}
else die('2');