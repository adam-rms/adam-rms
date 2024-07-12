<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

//Similar setup can be found in the link provider api endpoint
$configObject = [
    "callback" => $CONFIG['ROOTURL'] . '/api/account/oauth-link/google.php',
    "keys" => [
        "id" => $CONFIGCLASS->get("AUTH_PROVIDERS_GOOGLE_KEYS_ID"),
        "secret" => $CONFIGCLASS->get("AUTH_PROVIDERS_GOOGLE_KEYS_SECRET")
    ],
    "scope" => $CONFIGCLASS->get("AUTH_PROVIDERS_GOOGLE_SCOPE"),
];
$adapter = new Hybridauth\Provider\Google($configObject);
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
    header("Location: " . $CONFIG['ROOTURL'] . "/user.php");
    exit;
}

$DBLIB->where("users_oauth_googleid", $userProfile->identifier);
$user = $DBLIB->getOne("users", ["users.users_userid"]);
if (strlen($userProfile->emailVerified) < 1) die('Please verify your email with Google before trying to link it to AdamRMS.' . '<a href="' . $CONFIG['ROOTURL'] . "/user.php" . '">Continue</a>');

if ($user and $user['users_userid'] != $AUTH->data['users_userid']) {
    //If its linked to another account remove the link to link it to this one
    $DBLIB->where("users_userid", $user['users_userid']);
    $DBLIB->update("users", ["users_oauth_googleid" => null]);
}

$DBLIB->where("users_userid", $AUTH->data['users_userid']);
$DBLIB->update("users", ["users_oauth_googleid" => $userProfile->identifier]);
header("Location: " . $CONFIG['ROOTURL'] . "/user.php");
exit;


/** @OA\Get(
 *     path="/account/oauth-link/google.php", 
 *     summary="Link OAuth - Google", 
 *     description="Link the OAuth provider Google to the user account", 
 *     operationId="oauth-link-google", 
 *     tags={"account"}, 
 *     @OA\Response(
 *         response="308", 
 *         description="Redirect",
 *     ), 
 * )
 */
