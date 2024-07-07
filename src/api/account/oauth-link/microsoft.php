<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

//Similar setup can be found in the link provider api endpoint
$configObject = [
    "callback" => $CONFIG['ROOTURL'] . '/api/account/oauth-link/microsoft.php',
    "keys" => [
        "id" => $CONFIGCLASS->get("AUTH_PROVIDERS_MICROSOFT_APP_ID"),
        "secret" => $CONFIGCLASS->get("AUTH_PROVIDERS_MICROSOFT_KEYS_SECRET")
    ],
    "scope" => "openid user.read",
    "tenant" => "common",
];
$adapter = new Hybridauth\Provider\MicrosoftGraph($configObject);

$adapter->authenticate();
$accessToken = $adapter->getAccessToken(); //We don't actually use this - we could in theory just drop it?
$userProfile = $adapter->getUserProfile();
$adapter->disconnect(); //Disconnect this authentication from the session, so they can pick another account
if (strlen($userProfile->identifier) < 1) {
    //ISSUE WITH PROFILE
    header("Location: " . $CONFIG['ROOTURL'] . "/user.php");
    exit;
}

$DBLIB->where("users_oauth_microsoftid", $userProfile->identifier);
$user = $DBLIB->getOne("users", ["users.users_userid"]);
if ($user and $user['users_userid'] != $AUTH->data['users_userid']) {
    //If its linked to another account remove the link to link it to this one
    $DBLIB->where("users_userid", $user['users_userid']);
    $DBLIB->update("users", ["users_oauth_microsoftid" => null]);
}

$DBLIB->where("users_userid", $AUTH->data['users_userid']);
$DBLIB->update("users", ["users_oauth_microsoftid" => $userProfile->identifier]);
header("Location: " . $CONFIG['ROOTURL'] . "/user.php");
exit;


/** @OA\Get(
 *     path="/account/oauth-link/microsoft.php", 
 *     summary="Link OAuth - Microsoft", 
 *     description="Link the OAuth provider Microsoft to the user account", 
 *     operationId="oauth-link-microsoft", 
 *     tags={"account"}, 
 *     @OA\Response(
 *         response="308", 
 *         description="Redirect",
 *     )
 * )
 */
