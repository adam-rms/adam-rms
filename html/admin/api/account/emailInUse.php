<?php
require_once __DIR__ . '/../apiHeadSecure.php';

header('Content-Type:text/plain');
	
	if (isset($_GET['email'])) {
		if ($bCMS->sanitizeString($_GET['email']) == $USERDATA['users_email']) die('1'); //Its ok - It is the user's current email address so therefore it isn't duplicate

        if ($AUTH->emailTaken($bCMS->sanitizeString($_GET['email']))) die('2');
		else die('1');
	} else die('Sorry - I think you are in the wrong place!');
?>