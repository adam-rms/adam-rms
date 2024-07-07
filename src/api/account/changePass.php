<?php
//Re-send a verification email
require_once __DIR__ . '/../apiHeadSecure.php';

if (hash($AUTH->data['users_hash'], $AUTH->data['users_salty1'] . $_POST['oldpass']. $AUTH->data['users_salty2']) != $AUTH->data['users_password']) finish(false,["message"=>"Current Password Incorrect"]);

$DBLIB->where ('users_userid', $AUTH->data['users_userid']);
if ($DBLIB->update('users', ["users_password" => hash($CONFIG['AUTH_NEXTHASH'], $AUTH->data['users_salty1'] . $_POST['newpass'] . $AUTH->data['users_salty2'])])) {
    $bCMS->auditLog("UPDATE", "users", "PASSWORD CHANGE", $AUTH->data['users_userid'],$AUTH->data['users_userid']);
    finish(true);
}
else finish(false);

/** @OA\Post(
 *      path="/account/changePass.php", 
 *      summary="Change Password", 
 *      description="Change the password of the current user", 
 *      operationId="changePassword", 
 *      tags={"account"}, 
 *      @OA\Response(
 *          response="200", 
 *          description="OK or Error",
 *          @OA\MediaType(
 *              mediaType="application/json", 
 *              @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *          ),
 *      ), 
 *      @OA\Parameter(
 *          name="oldpass",
 *          in="query",
 *          description="undefined",
 *          required="true", 
 *          @OA\Schema(
 *              type="string"), 
 *          ), 
 *      @OA\Parameter(
 *          name="newpass",
 *          in="query",
 *          description="undefined",
 *          required="true", 
 *          @OA\Schema(
 *              type="string"), 
 *          ), 
 *  )
 */