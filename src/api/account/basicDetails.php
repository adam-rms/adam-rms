<?php
require_once __DIR__ . '/../apiHeadSecure.php';

$array = [];
if (!isset($_POST['formData'])) die("404");
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}

$userid = $array['users_userid'];
if (!$AUTH->serverPermissionCheck("USERS:EDIT")) $userid = $AUTH->data['users_userid'];

$DBLIB->where("users.users_deleted", 0);
$DBLIB->where("users.users_suspended", 0);
$DBLIB->where("users.users_userid", $userid);
$currentUserData = $DBLIB->getone("users", ["users.*"]);
if (!$currentUserData) finish(false, ["message" => "User not found error"]);

$array["users_email"] = strtolower($array["users_email"]);
$array["users_username"] = strtolower($array["users_username"]);

// Restrict the fields that can be edited
$array = array_intersect_key($array, array_flip(["users_username", "users_name1", "users_name2", "users_email", "users_social_facebook", "users_social_twitter", "users_social_instagram", "users_social_linkedin", "users_social_snapchat"]));

if ($array["users_email"] != $currentUserData["users_email"] and $AUTH->emailTaken($array["users_email"])) finish(false, ["message" => "Sorry this email is in use for another account"]);
if ($array["users_username"] != $currentUserData["users_username"] and $AUTH->usernameTaken($array["users_username"])) finish(false, ["message" => "Sorry this username is in use for another account, please pick another"]);

if ($array["users_email"] != $currentUserData["users_email"]) $array["users_emailVerified"] = 0;
else $array["users_emailVerified"] = $currentUserData["users_emailVerified"];

$DBLIB->where("users_userid", $userid);
$result = $DBLIB->update("users", $array, 1);
if (!$result) finish(false, ["code" => "UPDATE-FAIL", "message"=> "Could not update account details"]);
else {
    if ($array["users_emailVerified"] == 0) $AUTH->verifyEmail($userid);
    $bCMS->auditLog("EDIT-ACCOUNT", "users", json_encode($array), $AUTH->data['users_userid'], $userid);
    finish(true);
}


/** @OA\Post(
 *     path="/account/basicDetails.php", 
 *     summary="Update User Details", 
 *     description="Update basic user details", 
 *     operationId="updateBasicDetails", 
 *     tags={"account"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="OK",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="404", 
 *         description="Not Found",
 *     ), 
 *     @OA\Response(
 *         response="default", 
 *         description="Error",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="formData",
 *         in="formData",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="users_userid", 
 *                 type="string", 
 *                 description="The ID of the user",
 *             ),
 *             @OA\Property(
 *                 property="users_email", 
 *                 type="string", 
 *                 description="The email address of the user",
 *             ),
 *             @OA\Property(
 *                 property="users_username", 
 *                 type="string", 
 *                 description="The username of the user",
 *             ),
 *             @OA\Property(
 *                 property="users_name1", 
 *                 type="string", 
 *                 description="The first name of the user",
 *             ),
 *             @OA\Property(
 *                 property="users_name2", 
 *                 type="string", 
 *                 description="The last name of the user",
 *             ),
 *             @OA\Property(
 *                 property="users_social_facebook", 
 *                 type="string", 
 *                 description="The Facebook username of the user",
 *             ),
 *             @OA\Property(
 *                 property="users_social_twitter", 
 *                 type="string", 
 *                 description="The Twitter username of the user",
 *             ),
 *             @OA\Property(
 *                 property="users_social_instagram", 
 *                 type="string", 
 *                 description="The Instagram username of the user",
 *             ),
 *             @OA\Property(
 *                 property="users_social_linkedin", 
 *                 type="string", 
 *                 description="The LinkedIn username of the user",
 *             ),
 *             @OA\Property(
 *                 property="users_social_snapchat", 
 *                 type="string", 
 *                 description="The Snapchat username of the user",
 *             ),
 *         ),
 *     ), 
 * )
 */