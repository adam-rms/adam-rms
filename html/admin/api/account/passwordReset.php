<?php
	require_once __DIR__ . '/../apiHead.php';

	header('Content-Type:text/plain');
	if (!isset($_GET['code'])) {
        header('Location: ' . $CONFIG['ROOTURL']); //If it fails we may as well just assume they have tried to click it a second time.
        exit;
	}

	$DBLIB->where('passwordResetCodes_code', $bCMS->sanitizeString($_GET['code']));
	$code = $DBLIB->getOne('passwordResetCodes');
	if (isset($code) and $code['passwordResetCodes_valid'] == '1') {
		if (strtotime($code['passwordResetCodes_timestamp']) < (time()-(60*60*48))) {
			//Code has expired - send another
            if ($AUTH->forgotPassword()) die("Sorry - Your code has expired - we've sent you another one instead");
            else die("Sorry - Your code has expired - please contact support");
        }
		$DBLIB->where ('users_userid', $code['users_userid']);
		$DBLIB->update ('users', ["users_password" => "RESET", "users_changepass" => 1]); //Verify E-Mail

		$DBLIB->where ('passwordResetCodes_id', $code['passwordResetCodes_id']);
		$DBLIB->update ('passwordResetCodes', ["passwordResetCodes_valid" => "0", "passwordResetCodes_used" => "1"]); //Verify E-Mail

		sendemail($code['users_userid'], "Password changed for " . $CONFIG['PROJECT_NAME'], '
			<center>
				<h1>Your password has been changed using a forgot password link</h1></center>
			');
		$bCMS->auditLog("UPDATE", "users", "PASSWORD RESET", $AUTH->data['users_userid'],$AUTH->data['users_userid']);
		$AUTH->generateToken($code['users_userid'], true);
		exit;
	} else {
		header('Location: ' . $CONFIG['ROOTURL']); //If it fails we may as well just assume they have tried to click it a second time.
        exit;
    }
?>
