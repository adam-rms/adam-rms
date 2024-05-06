<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['instances_id']) or strlen($_POST['instances_id']) < 1) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

$userEmailDomain = array_pop(explode('@', $AUTH->data['users_email']));

$DBLIB->where("instances_id",$_POST['instances_id']);
$DBLIB->where("instances_deleted",0);
$DBLIB->where("instances_trustedDomains IS NOT NULL");
$instance = $DBLIB->getOne("instances",["instances_id","instances_trustedDomains"]);
if (!$instance) finish(false, ["code" => "AUTH-ERROR", "message"=> "Instance cannot be joined"]);
$instance['trustedDomains'] = json_decode($instance['instances_trustedDomains'],true);

if ($AUTH->data['instance_ids'] and in_array($instance['instances_id'],$AUTH->data['instance_ids'])) finish(false, ["code" => "ALREADY-IN-INSTANCE", "message"=> "Already in Business"]);
elseif ($AUTH->data['users_emailVerified'] != 1) finish(false, ["code" => "AUTH-ERROR", "message"=> "Email not verified"]);
elseif (!$instance['trustedDomains']['domains'] or count($instance['trustedDomains']['domains']) < 1 or !$instance['trustedDomains']['instancePositions_id']) finish(false, ["code" => "AUTH-ERROR", "message"=> "No trusted domains"]);
elseif (!in_array($userEmailDomain,$instance['trustedDomains']['domains']))  finish(false, ["code" => "AUTH-ERROR", "message"=> "Not in trusted domains"]);
  
$DBLIB->where("instancePositions_id", $instance['trustedDomains']['instancePositions_id']);
$position = $DBLIB->getone("instancePositions", ["instancePositions_id"]);
if (!$position) finish(false, ["code" => "ADD-USER-TO-INSTANCE-FAIL-NOPOSITION", "message"=> "Could not find position to add to Business"]);

if (!$bCMS->instanceHasUserCapacity($instance['instances_id'])) finish(false, ["code" => "AUTH-ERROR", "message" => "Could not add user to Business, business is full"]);

if (!$DBLIB->insert("userInstances", [
    "users_userid" => $AUTH->data['users_userid'],
    "instancePositions_id" => $position["instancePositions_id"],
    "userInstances_label" => $instance['trustedDomains']['userInstances_label']
])) finish(false, ["code" => "ADD-USER-TO-INSTANCE-FAIL", "message"=> "Could not add user to Business"]);

//Get all users in this instance to notify them
$DBLIB->where("users_deleted", 0);
$DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
$DBLIB->where("instances_id",  $instance['instances_id']);
$DBLIB->where("userInstances.userInstances_deleted",  0);
$DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
$users = $DBLIB->get('users', null, ["users.users_userid"]);
foreach ($users as $user) {
    if ($user != $AUTH->data['users_userid']) {
        notify(30,$user['users_userid'], $instance['instances_id'], $AUTH->data['users_name1'] . " " . $AUTH->data['users_name2'] . " added to business", $AUTH->data['users_name1'] . " " . $AUTH->data['users_name2'] . " has been added to the business by being in a trusted domain. This has given them the role name " . $instance['trustedDomains']['userInstances_label']);
    }
}

finish(true);

/** @OA\Post(
 *     path="/instances/addUserFromTrustedDomain.php", 
 *     summary="Join Instance using Trusted Domain", 
 *     description="Add a user to an instance from a trusted domain", 
 *     operationId="addUserToInstanceFromTrustedDomain", 
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
 *         name="instances_id",
 *         in="query",
 *         description="The instance id",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */