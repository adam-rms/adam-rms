<?php
//Re-send a verification email
require_once __DIR__ . '/../apiHeadSecure.php';

header("Content-Type: text/plain");

if ($PAGEDATA['USERDATA']['users_changepass'] != '1') die('Error'); //This page only works if the user if forced

$DBLIB->where ('users_userid', $PAGEDATA['USERDATA']['users_userid']);
if ($DBLIB->update('users', ["users_password" => hash($CONFIG['AUTH_NEXTHASH'], $PAGEDATA['USERDATA']['users_salty1'] . $_POST['pass'] . $PAGEDATA['USERDATA']['users_salty2']), "users_changepass" => 0])) {
    $bCMS->auditLog("UPDATE", "users", "PASSWORD CHANGE BECAUSE FORCED TO", $AUTH->data['users_userid'],$AUTH->data['users_userid']);
    die('1');
}
else die('2');

/** @OA\Post(
 *     path="/account/forcePasswordChange.php", 
 *     summary="Force Password Change", 
 *     description="Force a user to change their password", 
 *     operationId="forcePasswordChange", 
 *     tags={"account"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="OK",
 *         @OA\MediaType(
 *             mediaType="text/plain", 
 *             @OA\Schema( 
 *                 type="string", 
 *                 ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="pass",
 *         in="query",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="users_userid",
 *         in="body",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="users_changepass",
 *         in="body",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */