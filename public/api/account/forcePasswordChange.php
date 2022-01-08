<?php
//Re-send a verification email
require_once __DIR__ . '/../apiHeadSecure.php';

header("Content-Type: text/plain");

if ($PAGEDATA['USERDATA']['users_changepass'] != '1') die('Error'); //This page only works if the user if forced

$DBLIB->where ('users_userid', $PAGEDATA['USERDATA']['users_userid']);
if ($DBLIB->update ('users', ["users_password" => hash($CONFIG['nextHash'], $PAGEDATA['USERDATA']['users_salty1'] . $_POST['pass']. $PAGEDATA['USERDATA']['users_salty2']), "users_changepass" => 0])) {
    $bCMS->auditLog("UPDATE", "users", "PASSWORD CHANGE BECAUSE FORCED TO", $AUTH->data['users_userid'],$AUTH->data['users_userid']);
    die('1');
}
else die('2');