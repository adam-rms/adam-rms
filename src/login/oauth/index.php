<?php

require_once __DIR__ . '/../third_party.php';
require_once __DIR__ . 'providers.php';

$provider = $_GET["provider"];

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

$profile_emailVerified = false;
$profile_email = $userProfile->email;

if (!empty($userProfile->emailVerified)) {
    $profile_emailVerified = true;
    $profile_email = $userProfile->emailVerified;
}

social_authenticate_user(
    provider: $provider,
    userId: $userProfile->identifier,
    firstName: $userProfile->firstName,
    lastName: $userProfile->lastName,
    email: $profile_email,
    emailVerified: $profile_emailVerified
);