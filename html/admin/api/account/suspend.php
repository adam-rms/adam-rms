<?php
require_once __DIR__ . '/../apiHeadSecure.php';
header("Content-Type: text/plain");

if (!$AUTH->permissionCheck(9) or !isset($_POST['userid'])) die("404");

$AUTH->destroyTokens($bCMS->sanitizeString($_POST['userid']));
$bCMS->auditLog("INVALIDATE ALL", "authTokens", null, $AUTH->data['users_userid'],$bCMS->sanitizeString($_POST["userid"]));

$DBLIB->where ('users_userid', $bCMS->sanitizeString($_POST['userid']));
if ($DBLIB->update ('users', ["users_suspended" => $bCMS->sanitizeString($_POST['suspendval'])])) {
    $bCMS->auditLog("UPDATE", "users", "SUSPEND " . $bCMS->sanitizeString($_POST['suspendval']), $AUTH->data['users_userid'],$bCMS->sanitizeString($_POST['userid']));
    die('1');
}
else die('2');
