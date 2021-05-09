<?php
require_once __DIR__ . '/../common/head.php';
use \Firebase\JWT\JWT;
$PAGEDATA['pageConfig'] = ["TITLE" => "Login"];

if (isset($_GET['app-oauth'])) {
	$_SESSION['return'] = false;
	$_SESSION['app-oauth'] = "com.bstudios.adamrms";
	if ($GLOBALS['AUTH']->login) {
		$token = $GLOBALS['AUTH']->generateToken($AUTH->data['users_userid'], false, null, true, "App OAuth - already logged in");
		$jwt = JWT::encode(array(
			"iss" => $CONFIG['ROOTURL'],
			"uid" => $AUTH->data['users_userid'],
			"token" => $token,
			"exp" => time()+21*24*60*60, //21 days token expiry
			"iat" => time()
		), $CONFIG['JWTKey']);
		header("Location: " . $_SESSION['app-oauth'] . "://oauth_callback?token=" . $jwt);
	}
} elseif (isset($_GET['logout'])) {
	$AUTH->logout();
	header("Location: " . $CONFIG['ROOTURL'] . "/");
	die('<meta http-equiv="refresh" content="0; url="' . $CONFIG['ROOTURL'] . "/" . '" />');
} elseif ($GLOBALS['AUTH']->login) {
	//If they're logged in, take them back to root - this fixes a loop redirect issue
	header("Location: " . $CONFIG['ROOTURL'] . "/");
	die('<meta http-equiv="refresh" content="0; url="' . $CONFIG['ROOTURL'] . "/" . '" />');
}

if (isset($_GET['signup'])) echo $TWIG->render('login/signup.twig', $PAGEDATA);
if (isset($_GET['login'])) echo $TWIG->render('login/login1.twig', $PAGEDATA);
echo $TWIG->render('login/login.twig', $PAGEDATA);
?>
