<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->permissionCheck(10)) die("Sorry - you can't access this page");
if (!(isset($_POST['userid']))) die("No uid passed");

if ($AUTH->generateToken($bCMS->sanitizeString($_POST['userid']), false, $AUTH->data['users_userid'])) {
    $bCMS->auditLog("VIEWSITEAS", "users", null, $AUTH->data['users_userid'],$bCMS->sanitizeString($_POST['userid']));
    header('Location: '. $CONFIG['ROOTURL']);
}
