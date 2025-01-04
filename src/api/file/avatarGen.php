<?php
require_once __DIR__ . '/../apiHeadSecure.php';

use LasseRafn\InitialAvatarGenerator\InitialAvatar;

if (!isset($_GET['users_userid']) or strlen($_GET['users_userid']) < 1) finish(false, ["message" => "Missing users_userid"]);
$DBLIB->where("users.users_deleted", 0);
$DBLIB->where("users.users_userid", $_GET['users_userid']);
$user = $DBLIB->getone("users", ["users_name1", "users_name2"]);
if (!$user) finish(false, ["message" => "User not found error"]);

header('Content-Type: image/svg+xml');
$avatar = new InitialAvatar();
$image = $avatar->gd()->name($user['users_name1'] . " " . $user['users_name2'])->size(96)->autoColor()->fontName('Arial, Helvetica, sans-serif')->generateSvg()->toXMLString();
die($image);

/** @OA\Get(
 *     path="/file/avatarGen.php", 
 *     summary="Generate user Avatar", 
 *     description="Generate a user avatar based on the Users' initials", 
 *     operationId="avatarGen", 
 *     tags={"file_uploads"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="image/svg+xml", 
 *             @OA\Schema( 
 *                 type="string", 
 *                 format="binary",
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="users_userid",
 *         in="query",
 *         description="The userid id",
 *         required="true", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ),
 * )
 */
