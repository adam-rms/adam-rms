<?php
	require_once __DIR__ . '/../apiHead.php';

	header('Content-Type:text/plain');
	if (!isset($_POST['code'])) {
        header('Location: ' . $CONFIG['ROOTURL']);
        exit;
	}

	$DBLIB->where('emailVerificationCodes_code', $bCMS->sanitizeString($_POST['code']));
	$DBLIB->where('emailVerificationCodes_valid', 1);
	$code = $DBLIB->getOne('emailVerificationCodes');
	if ($code) {
		if (strtotime($code['emailVerificationCodes_timestamp']) < (time()-(60*60*48))) {
			//Code has expired - send another
            if ($AUTH->verifyEmail()) die("Sorry - Your code has expired - we've sent you another one instead");
            else die("Sorry - Your code has expired - please try to create another code or contact the support team");
        }
		$DBLIB->where ('users_userid', $code['users_userid']);
		$DBLIB->update ('users', ["users_emailVerified" => "1"]); //Verify E-Mail

		$DBLIB->where ('emailVerificationCodes_id', $code['emailVerificationCodes_id']);
		$DBLIB->update ('emailVerificationCodes', ["emailVerificationCodes_valid" => "0", "emailVerificationCodes_used" => "1"]); //Verify E-Mail
		notify(3,$code['users_userid'], false, "Email address verified for " . $CONFIG['PROJECT_NAME'], '
				<h1 style="margin: 0 0 10px 0; font-family: sans-serif; font-size: 25px; line-height: 30px; color: #333333; font-weight: normal;">Thanks for verifying your email address</h1>
				<h2 style="margin: 0 0 10px 0; font-family: sans-serif; font-size: 18px; line-height: 22px; color: #333333; font-weight: bold;">Welcome to ' . $CONFIG['PROJECT_NAME'] . ' - We are delighted to have you onboard!</h2>
			');
		$bCMS->auditLog("UPDATE", "users", "VERIFY EMAIL", $AUTH->data['users_userid'],$AUTH->data['users_userid']);
		header('Location: ' . $CONFIG['ROOTURL']); exit;
	} else {
		header('Location: ' . $CONFIG['ROOTURL']); //If it fails we may as well just assume they have tried to click it a second time.
        exit;
    }

/** @OA\Post(
 *     path="/account/verifyEmail.php", 
 *     summary="Verify Email", 
 *     description="Verify an email address", 
 *     operationId="verifyEmail", 
 *     tags={"account"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Error",
 *         @OA\MediaType(
 *             mediaType="text/plain", 
 *             @OA\Schema( 
 *                 type="string", 
 *             ),
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
 *             type="string"
 * 		   ), 
 *     ), 
 * )
 */
