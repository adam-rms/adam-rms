<?php
require_once __DIR__ . '/../common/head.php';

use \Firebase\JWT\JWT;

$PAGEDATA['pageConfig'] = ["TITLE" => "Login"];
$PAGEDATA['googleAuthAvailable'] = $CONFIGCLASS->get("AUTH_PROVIDERS_GOOGLE_KEYS_ID") != false and $CONFIGCLASS->get("AUTH_PROVIDERS_GOOGLE_KEYS_SECRET") != false;
$PAGEDATA['microsoftAuthAvailable'] = $CONFIGCLASS->get("AUTH_PROVIDERS_MICROSOFT_APP_ID") != false and $CONFIGCLASS->get("AUTH_PROVIDERS_MICROSOFT_KEYS_SECRET") != false;


if (isset($_GET['app-oauth'])) {
	$_SESSION['return'] = false;
	if (isset($_GET['returnHost'])) {
		$_SESSION['app-oauth'] = 'https://' . $_GET['returnHost'] . '/';
	} else {
		$_SESSION['app-oauth'] = "com.bstudios.adamrms://";
	}
	if ($GLOBALS['AUTH']->login) {
		$AUTH->logout();
	}
} elseif (isset($_GET['logout'])) {
	$AUTH->logout();
	header("Location: " . $CONFIG['ROOTURL'] . "/");
	die('<meta http-equiv="refresh" content="0; url="' . $CONFIG['ROOTURL'] . "/" . '" />');
} elseif ($GLOBALS['AUTH']->login and !isset($_GET['app-magiclink'])) {
	//If they're logged in, take them back to root - this fixes a loop redirect issue
	header("Location: " . $CONFIG['ROOTURL'] . "/");
	die('<meta http-equiv="refresh" content="0; url="' . $CONFIG['ROOTURL'] . "/" . '" />');
} 

if (isset($_GET['signup'])) echo $TWIG->render('login/signup.twig', $PAGEDATA);
elseif (isset($_GET['app-magiclink']) and (in_array($_GET['app-magiclink'], $GLOBALS['AUTH']->VALIDMAGICLINKREDIRECTS) or $CONFIG['DEV'])) {
	if (isset($_GET['magic-token'])) {
		$url = $_GET['app-magiclink'] . "?token=" . $_GET['magic-token'] . "&referer=" . urlencode($CONFIG['ROOTURL']);
		header("Location: " . $url);
		die('<meta http-equiv="refresh" content="0; url="' . $url . '" />');
	}
	$PAGEDATA['MAGICLINKURL'] = $_GET['app-magiclink'];
	echo $TWIG->render('login/magicLink.twig', $PAGEDATA);
} else {
	$PAGEDATA['runLightFocusAnimation'] = true;
	echo $TWIG->render('login/login.twig', $PAGEDATA);
}
