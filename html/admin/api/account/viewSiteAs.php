<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->serverPermissionCheck("USERS:VIEW_SITE_AS")) die("Sorry - you can't access this page");
if (!(isset($_POST['userid']))) die("No uid passed");

if ($AUTH->generateToken($bCMS->sanitizeString($_POST['userid']), $AUTH->data['users_userid'], "Web - View Site As", "web-session")) {
    $bCMS->auditLog("VIEWSITEAS", "users", null, $AUTH->data['users_userid'],$bCMS->sanitizeString($_POST['userid']));
    header('Location: '. $CONFIG['ROOTURL']);
}
