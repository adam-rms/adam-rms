<?php
require_once 'loginAjaxHead.php';

if (isset($_POST['email'])) {
    if (emailTaken($GLOBALS['bCMS']->sanitizeString(strtolower($_POST['email'])))) finish(true, null, true);
    else finish(true, null, false);
} elseif (isset($_POST['username'])) {
    if (usernameTaken($GLOBALS['bCMS']->sanitizeString(strtolower($_POST['username'])))) finish(true, null, true);
    else finish(true, null, false);
} else die('Sorry - I think you are in the wrong place!');

?>
