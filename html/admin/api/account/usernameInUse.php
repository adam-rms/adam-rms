<?php
require_once __DIR__ . '/../apiHeadSecure.php';
	
	header('Content-Type:text/plain');
	
	if (isset($_POST['username'])) {
        if ($bCMS->sanitizeString($_POST['username']) == $USERDATA['users_username']) die('1'); //Its ok - It is the user's current username so therefore it isn't duplicate

        if ($AUTH->usernameTaken($bCMS->sanitizeString($_POST['username']))) die('2');
        else die('1');
	} else die('Sorry - I think you are in the wrong place!');
?>