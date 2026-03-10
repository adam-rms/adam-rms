<?php
require_once __DIR__ . '/../apiHead.php';

$resetCode = $_GET['code'] ?? $_POST['code'] ?? null;

if (!$resetCode) {
	header('Location: ' . $CONFIG['ROOTURL']);
	exit;
}

$DBLIB->where('passwordResetCodes_code', $bCMS->sanitizeString($resetCode));
$code = $DBLIB->getOne('passwordResetCodes');
if (!isset($code) or $code['passwordResetCodes_valid'] != '1') {
	header('Location: ' . $CONFIG['ROOTURL']); //If it fails we may as well just assume they have tried to click it a second time.
	exit;
}

if (strtotime($code['passwordResetCodes_timestamp']) < (time() - (60 * 60 * 48))) { //48 hours
	//Code has expired - send another
	if ($AUTH->forgotPassword($code['users_userid'])) die("Sorry - Your code has expired - we've sent you another one instead");
	else die("Sorry - Your code has expired - please try to create another code or contact the support team");
}

// On GET, show a confirmation page rather than immediately processing the reset.
// This prevents email security scanners (which pre-fetch links) from consuming the one-time code.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Content-Type: text/html; charset=utf-8');
	echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
	echo '<title>Reset Password - ' . htmlspecialchars($CONFIG['PROJECT_NAME']) . '</title>';
	echo '<style>body{font-family:sans-serif;background:#222;color:#333;margin:0;padding:0;display:flex;justify-content:center;align-items:center;min-height:100vh}';
	echo '.card{background:#fff;border-radius:8px;padding:40px;max-width:400px;width:90%;text-align:center;box-shadow:0 2px 10px rgba(0,0,0,0.3)}';
	echo 'h1{font-size:22px;margin:0 0 15px}p{font-size:15px;line-height:1.5;color:#555;margin:0 0 25px}';
	echo 'button{background:#333;color:#fff;border:none;padding:12px 30px;font-size:16px;border-radius:4px;cursor:pointer}';
	echo 'button:hover{background:#555}</style></head><body>';
	echo '<div class="card"><h1>Reset Your Password</h1>';
	echo '<p>Click the button below to confirm your password reset for ' . htmlspecialchars($CONFIG['PROJECT_NAME']) . '.</p>';
	echo '<form method="POST"><input type="hidden" name="code" value="' . htmlspecialchars($resetCode) . '">';
	echo '<button type="submit">Reset My Password</button></form></div></body></html>';
	exit;
}

// POST: actually process the reset
$DBLIB->where('users_userid', $code['users_userid']);
$DBLIB->update('users', ["users_password" => "RESET", "users_changepass" => 1]);

$DBLIB->where('passwordResetCodes_id', $code['passwordResetCodes_id']);
$DBLIB->update('passwordResetCodes', ["passwordResetCodes_valid" => "0", "passwordResetCodes_used" => "1"]);
notify(1, $code['users_userid'], false, "Password changed for " . $CONFIG['PROJECT_NAME'] . ' using a forgot password link');

$bCMS->auditLog("UPDATE", "users", "PASSWORD RESET", $code['users_userid'], $code['users_userid']);
$AUTH->generateToken($code['users_userid'], false, "Web", "web-session");
$AUTH->redirectToReturnAddress();
exit;

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
