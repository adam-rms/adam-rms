<?php

use function Clue\StreamFilter\fun;

require_once __DIR__ . '/../../common/head.php';
require_once __DIR__ . '/../hybridauth.php';

$provider = "Google";

try {
    $adapter = $hybridauth->authenticate($provider);
} catch (\Exception $e) {
    //Issue with auth state, which is a problem with the user's browser. We can't do anything about this, so just show an error
    $PAGEDATA['ERROR'] = "Sorry, something went wrong authenticating with {$provider}.";
    die($TWIG->render('login/error.twig', $PAGEDATA));
    exit;
}

$userProfile = $adapter->getUserProfile();
$adapter->disconnect(); //Disconnect this authentication from the session, so they can pick another account

if (strlen($userProfile->identifier) < 1) {
    //ISSUE WITH PROFILE
    $PAGEDATA['ERROR'] = "Sorry, something went wrong authenticating with {$provider}";
    echo $TWIG->render('login/error.twig', $PAGEDATA);
    exit;
}


$DBLIB->where("users_oauth_microsoftid", $userProfile->identifier);
$DBLIB->where("users_deleted", 0);
$user = $DBLIB->getOne("users", ["users.users_suspended", "users.users_userid", "users.users_hash", "users.users_email"]);
if ($user) {
    if ($user['users_suspended'] != '0') {
        $PAGEDATA['ERROR'] = "Sorry, your user account is suspended";
        echo $TWIG->render('login/error.twig', $PAGEDATA);
        exit;
    }

    if ($user['users_emailVerified'] != 1 and strtolower($userProfile->emailVerified ?? "") == strtolower($user['users_email'])) {
        // Update their email verification status
        $DBLIB->where("users_userid", $user['users_userid']);
        $DBLIB->update("users", ["users_emailVerified" => 1]);
    }

    $GLOBALS['AUTH']->generateToken($user['users_userid'], false, "Web - {$provider}", "web-session");
    header("Location: " . (isset($_SESSION['return']) ? $_SESSION['return'] : $CONFIG['ROOTURL']));
    exit;
} else {
    //See if an email is found, but not linked to microsoft. We don't want to auto-link them because its a good attack vector, so instead prompt a password login and then link in account settings.
    $DBLIB->where("users_email", strtolower($userProfile->email));
    $user = $DBLIB->getOne("users", ["users.users_suspended", "users.users_userid", "users.users_hash"]);
    if ($user) {
        $PAGEDATA['ERROR'] = "An AdamRMS account associated with the email address you selected has been found. Please login again using your AdamRMS username & password to link your account to a Microsoft Account in AdamRMS account settings";
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
    'users_oauth_microsoftid' => $userProfile->identifier,
    'users_username' => $username,
    'users_name1' => $userProfile->firstName,
    'users_name2' => $userProfile->lastName,
    'users_hash' => $CONFIG['AUTH_NEXTHASH']
);
if ($userProfile->emailVerified) {
    $data['users_email'] = strtolower($userProfile->emailVerified);
    $data['users_emailVerified'] = 1;
} else {
    $data['users_email'] = strtolower($userProfile->email);
    $data['users_emailVerified'] = 0;
}

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
    $GLOBALS['AUTH']->generateToken($newUser, false, "Web - {$provider}", "web-session");
    header("Location: " . (isset($_SESSION['return']) ? $_SESSION['return'] : $CONFIG['ROOTURL']));
    exit;
}
