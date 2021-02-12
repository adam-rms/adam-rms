<?php
//Re-send a verification email
require_once __DIR__ . '/../apiHeadSecure.php';
header("Content-Type: text/plain");

if (hash($PAGEDATA['USERDATA']['users_hash'], $PAGEDATA['USERDATA']['users_salty1'] . $_POST['oldpass']. $PAGEDATA['USERDATA']['users_salty2']) != $PAGEDATA['USERDATA']['users_password']) die('2');

$DBLIB->where ('users_userid', $PAGEDATA['USERDATA']['users_userid']);
if ($DBLIB->update ('users', ["users_password" => hash($CONFIG['nextHash'], $PAGEDATA['USERDATA']['users_salty1'] . $_POST['newpass']. $PAGEDATA['USERDATA']['users_salty2'])])) {
    $bCMS->auditLog("UPDATE", "users", "PASSWORD CHANGE", $AUTH->data['users_userid'],$AUTH->data['users_userid']);
    die('1');
}
else die('2');