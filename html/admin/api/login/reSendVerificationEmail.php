<?php
require_once 'loginAjaxHead.php';

if (isset($_POST['userid'])) {
    if ($AUTH->verifyEmail($GLOBALS['bCMS']->sanitizeString($_POST['userid']))) {
        $bCMS->auditLog("UPDATE", "users", "RESEND VERIFICATION EMAIL", $GLOBALS['bCMS']->sanitizeString($_POST['userid']),$GLOBALS['bCMS']->sanitizeString($_POST['userid']));
        finish(true, null, true);
    }
    else finish(true, null, false);
} else die("Sorry - page not found")
?>
