<?php
    require_once __DIR__ . '/../apiHeadSecure.php';
    header('Content-Type:text/plain');

    $userid = $PAGEDATA['USERDATA']['users_userid'];

    $isDarkTheme = isset($_POST['dark']);


    $DBLIB->where("users_userid", $userid);
    if ($DBLIB->update ('users', ["users_dark_mode" => $isDarkTheme ? 1 : 0])) {
        die("1");
    }
    else die("0");


