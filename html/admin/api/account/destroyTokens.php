<?php
require_once __DIR__ . '/../apiHeadSecure.php';
header("Content-Type: text/plain");


if (!$AUTH->permissionCheck(7) or !isset($_GET['userid'])) die("404");

if ($AUTH->destroyTokens($bCMS->sanitizeString($_GET['userid']))) {
    $bCMS->auditLog("INVALIDATE ALL", "authTokens", null, $AUTH->data['users_userid'],$bCMS->sanitizeString($_GET["userid"]));
    die("1");
} else die("2");


