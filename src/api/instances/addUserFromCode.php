<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['signupCodes_name']) or strlen($_POST['signupCodes_name']) < 1) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

$DBLIB->where("signupCodes_name",$_POST['signupCodes_name']);
$DBLIB->where("signupCodes_valid",1);
$DBLIB->where("signupCodes_deleted",0);
$DBLIB->where("instancePositions_id IS NOT NULL");
$DBLIB->where("signupCodes_role IS NOT NULL");
$code = $DBLIB->getone("signupCodes");
if (!$code) {
    sleep (10); //Wait 10 seconds as a rudimentary form of brute force protection
    finish(false, ["code" => "AUTH-ERROR", "message"=> "Code not valid"]);
}

if ($AUTH->data['instance_ids'] and in_array($code['instances_id'],$AUTH->data['instance_ids'])) finish(false, ["code" => "ALREADY-IN-INSTANCE", "message"=> "Already in Business"]);

$DBLIB->where("instancePositions_id", $code['instancePositions_id']);
$position = $DBLIB->getone("instancePositions", ["instancePositions_id"]);
if (!$position) finish(false, ["code" => "ADD-USER-TO-INSTANCE-FAIL-NOPOSITION", "message"=> "Could not add user to Business"]);

if (!$bCMS->instanceHasUserCapacity($code['instances_id'])) finish(false, ["code" => "AUTH-ERROR", "message" => "Could not add user to Business, business is full"]);

if (!$DBLIB->insert("userInstances", [
    "users_userid" => $AUTH->data['users_userid'],
    "instancePositions_id" => $position["instancePositions_id"],
    "userInstances_label" => $code['signupCodes_role'],
    "signupCodes_id" => $code['signupCodes_id']
])) finish(false, ["code" => "ADD-USER-TO-INSTANCE-FAIL", "message"=> "Could not add user to Business"]);

//Get all users in this instance
$DBLIB->where("users_deleted", 0);
$DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
$DBLIB->where("instances_id",  $code['instances_id']);
$DBLIB->where("userInstances.userInstances_deleted",  0);
$DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
$users = $DBLIB->get('users', null, ["users.users_userid"]);
foreach ($users as $user) {
    if ($user != $AUTH->data['users_userid']) {
        notify(30,$user['users_userid'], $code['instances_id'], $AUTH->data['users_name1'] . " " . $AUTH->data['users_name2'] . " added to business", $AUTH->data['users_name1'] . " " . $AUTH->data['users_name2'] . " has been added to the business by using signup code " . $code['signupCodes_name'] . " which has given them the role name " . $code["signupCodes_role"]);
    }
}

finish(true);

/** @OA\Post(
 *     path="/instances/addUserFromCode.php", 
 *     summary="Join Instance using Signup Code", 
 *     description="Add a user to an instance from a signup code", 
 *     operationId="addUserToInstanceFromCode", 
 *     tags={"instances"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *                 @OA\Property(
 *                     property="response", 
 *                     type="array", 
 *                     description="A null Array",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="default", 
 *         description="Error",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="signupCodes_name",
 *         in="query",
 *         description="The signup code",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */