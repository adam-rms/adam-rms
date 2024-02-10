<?php
require_once __DIR__ . '/../apiHead.php';

header('Content-Type:text/plain');
if (!isset($_POST['code'])) {
	header('Location: ' . $CONFIG['ROOTURL']); //If it fails we may as well just assume they have tried to click it a second time.
	exit;
}

$DBLIB->where('passwordResetCodes_code', $bCMS->sanitizeString($_POST['code']));
$code = $DBLIB->getOne('passwordResetCodes');
if (isset($code) and $code['passwordResetCodes_valid'] == '1') {
	if (strtotime($code['passwordResetCodes_timestamp']) < (time() - (60 * 60 * 48))) { //48 hours
		//Code has expired - send another
		if ($AUTH->forgotPassword()) die("Sorry - Your code has expired - we've sent you another one instead");
		else die("Sorry - Your code has expired - please try to create another code or contact the support team");
	}
	$DBLIB->where('users_userid', $code['users_userid']);
	$DBLIB->update('users', ["users_password" => "RESET", "users_changepass" => 1]); //Verify E-Mail

	$DBLIB->where('passwordResetCodes_id', $code['passwordResetCodes_id']);
	$DBLIB->update('passwordResetCodes', ["passwordResetCodes_valid" => "0", "passwordResetCodes_used" => "1"]); //Verify E-Mail
	notify(1, $code['users_userid'], false, "Password changed for " . $CONFIG['PROJECT_NAME'] . ' using a forgot password link');

	$bCMS->auditLog("UPDATE", "users", "PASSWORD RESET", $AUTH->data['users_userid'], $AUTH->data['users_userid']);
	$AUTH->generateToken($code['users_userid'], false, "Web", "web-session");
	$AUTH->redirectToReturnAddress();
} else {
	header('Location: ' . $CONFIG['ROOTURL']); //If it fails we may as well just assume they have tried to click it a second time.
	exit;
}

/** @OA\Post(
 *     path="/account/passwordReset.php", 
 *     summary="Password Reset", 
 *     description="Act on a password reset code", 
 *     operationId="passwordReset", 
 *     tags={"account"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Error",
 *         @OA\MediaType(
 *             mediaType="text/plain", 
 *             @OA\Schema( 
 *                 type="string", 
 *                 ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="308", 
 *         description="Success",
 *     ), 
 *     @OA\Parameter(
 *         name="code",
 *         in="query",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */