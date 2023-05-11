<?php
require_once __DIR__ . '/../common/head.php';

use \Firebase\JWT\JWT;

$PAGEDATA['pageConfig'] = ["TITLE" => "Login"];

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
elseif (isset($_GET['login'])) echo $TWIG->render('login/login.twig', $PAGEDATA);
elseif (isset($_GET['app-magiclink']) and (in_array($_GET['app-magiclink'], $GLOBALS['AUTH']->VALIDMAGICLINKREDIRECTS) or $CONFIG['DEV'])) {
	if (isset($_GET['magic-token'])) {
		$url = $_GET['app-magiclink'] . "?token=" . $_GET['magic-token'] . "&referer=" . urlencode($CONFIG['ROOTURL']);
		header("Location: " . $url);
		die('<meta http-equiv="refresh" content="0; url="' . $url . '" />');
	}
	$PAGEDATA['MAGICLINKURL'] = $_GET['app-magiclink'];
	echo $TWIG->render('login/magicLink.twig', $PAGEDATA);
} elseif (isset($_GET['google'])) {
	//Similar setup can be found in the link provider api endpoint
	$CONFIG['AUTH-PROVIDERS']['GOOGLE']['callback'] = $CONFIG['ROOTURL'] . '/login/index.php?google';

	$adapter = new Hybridauth\Provider\Google($CONFIG['AUTH-PROVIDERS']['GOOGLE']);
	/**
	 * 3. Sign in a user with Google
	 *
	 * Hybridauth will attempt to negotiate with the Google api and authenticate the user.
	 * This call will basically do one of 3 things...
	 * 1) Redirect (with exit) away to show an authentication screen for a provider (e.g. Facebook's OAuth confirmation page)
	 * 2) Finalize an incoming authentication and store access data in a session
	 * 3) Confirm a session exists and do nothing
	 * If for whatever reason the process fails, Hybridauth will then throw an exception.
	 *
	 * Note that if the user is already authenticated, then any subsequent call to this method will be ignored.
	 */
	$adapter->authenticate();
	$accessToken = $adapter->getAccessToken(); //We don't actually use this - we could in theory just drop it?
	$userProfile = $adapter->getUserProfile();
	$adapter->disconnect(); //Disconnect this authentication from the session, so they can pick another account
	if (strlen($userProfile->identifier) < 1) {
		//ISSUE WITH PROFILE
		$PAGEDATA['ERROR'] = "Sorry, something went wrong authenticating with Google";
		echo $TWIG->render('login/error.twig', $PAGEDATA);
		exit;
	}
	if (strlen($userProfile->emailVerified) < 1) {
		$PAGEDATA['ERROR'] = "Please verify your email with Google to continue to login";
		echo $TWIG->render('login/error.twig', $PAGEDATA);
		exit;
	}

	$DBLIB->where("users_oauth_googleid", $userProfile->identifier);
	$DBLIB->where("users_deleted", 0);
	$user = $DBLIB->getOne("users", ["users.users_suspended", "users.users_userid", "users.users_hash", "users.users_emailVerified", "users.users_email"]);
	if ($user) {
		if ($user['users_suspended'] != '0') {
			$PAGEDATA['ERROR'] = "Sorry, your user account is suspended";
			echo $TWIG->render('login/error.twig', $PAGEDATA);
			exit;
		} 
		
		if ($user['users_emailVerified'] != 1 and strtolower($userProfile->emailVerified) == strtolower($user['users_email'])) {
			// Update their email verification status
			$DBLIB->where("users_userid", $user['users_userid']);
			$DBLIB->update("users", ["users_emailVerified" => 1]);
		} 

		//Log them in successfully - duplicated below for signup
		if (!$_SESSION['return'] and isset($_SESSION['app-oauth'])) {
			$token = $GLOBALS['AUTH']->generateToken($user['users_userid'], false, "App OAuth - Google", "app-v1");
			$jwt = $GLOBALS['AUTH']->issueJWT($token, $user['users_userid'], "app-v1");
			header("Location: " . $_SESSION['app-oauth'] . "oauth_callback?token=" . $jwt);
			exit;
		} else {
			$GLOBALS['AUTH']->generateToken($user['users_userid'], false, "Web - Google", "web-session");
			header("Location: " . (isset($_SESSION['return']) ? $_SESSION['return'] : $CONFIG['ROOTURL']));
			exit;
		}
	} else {
		//See if an email is found, but not linked to google. We don't want to auto-link them because its a good attack vector, so instead prompt a password login and then link in account settings.
		$DBLIB->where("users_email", strtolower($userProfile->emailVerified));
		$user = $DBLIB->getOne("users", ["users.users_suspended", "users.users_userid", "users.users_hash", "users.users_emailVerified"]);
		if ($user) {
			$PAGEDATA['ERROR'] = "An AdamRMS account associated with the email address you selected has been found. Please login again using your AdamRMS username & password to link your account to a Google Account in AdamRMS account settings";
			echo $TWIG->render('login/error.twig', $PAGEDATA);
			exit;
		}
	}

	//Okay we can't find them, so lets sign them up to an account
	$username = preg_replace("/[^a-zA-Z0-9]+/", "", $userProfile->firstName . $userProfile->lastName);
	while ($AUTH->usernameTaken($username)) {
		$username .= "1";
	}
	$data = array(
		'users_email' => strtolower($userProfile->emailVerified),
		'users_emailVerified' => 1,
		'users_oauth_googleid' => $userProfile->identifier,
		'users_username' => $username,
		'users_name1' => $userProfile->firstName,
		'users_name2' => $userProfile->lastName,
		'users_hash' => $CONFIG['nextHash']
	);
	$newUser = $DBLIB->insert("users", $data);
	if (!$newUser) {
		$PAGEDATA['ERROR'] = "Sorry something went wrong trying to create a new user account";
		echo $TWIG->render('login/error.twig', $PAGEDATA);
		exit;
	}
	$bCMS->auditLog("INSERT", "users", json_encode($data), null, $newUser);
	if (!$_SESSION['return'] and isset($_SESSION['app-oauth'])) {
		$PAGEDATA['ERROR'] = "Account created - please restart app and login again";
		echo $TWIG->render('login/error.twig', $PAGEDATA);
		exit;
	} else {
		$GLOBALS['AUTH']->generateToken($user['users_userid'], false, "Web - Google", "web-session");
		header("Location: " . (isset($_SESSION['return']) ? $_SESSION['return'] : $CONFIG['ROOTURL']));
		exit;
	}
} else echo $TWIG->render('login/index.twig', $PAGEDATA);
