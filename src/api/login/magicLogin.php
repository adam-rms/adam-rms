<?php
require_once 'loginAjaxHead.php';

if (isset($_POST['formInput'])) {
    if ($AUTH->sendMagicLink($GLOBALS['bCMS']->sanitizeString($_POST['formInput']), $GLOBALS['bCMS']->sanitizeString($_POST['redirect']))) finish(true);
    else finish(false);
} else die("Sorry - page not found");
?>
