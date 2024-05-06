<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:USERS:CREATE:ADD_USER_BY_EMAIL") or !isset($_POST['rolegroup'])) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

if (count($_POST['users']) < 1) finish(true);
if (!$bCMS->instanceHasUserCapacity($AUTH->data['instance']['instances_id'])) finish(false, ["code" => "AUTH-ERROR", "message" => "Could not add user to Business, business is full"]);

foreach ($_POST['users'] as $user) {
    $DBLIB->where("instancePositions_id", $_POST['rolegroup']);
    $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
    $position = $DBLIB->getone("instancePositions", ["instancePositions_id"]);
    if (!$position) finish(false, ["code" => "ADD-USER-TO-INSTANCE-FAIL-NOPOSITION", "message"=> "Could not add user to Business"]);

    if (!$DBLIB->insert("userInstances", [
        "users_userid" => $user,
        "instancePositions_id" => $position["instancePositions_id"],
        "userInstances_label" => $_POST['rolename']
    ])) finish(false, ["code" => "ADD-USER-TO-INSTANCE-FAIL", "message"=> "Could not add user to Business"]);
   notify(2,$user, $AUTH->data['instance']['instances_id'], $AUTH->data['users_name1'] . " " . $AUTH->data['users_name2'] . " added you to " . $AUTH->data['instance']['instances_name'], false, "/api/instances/addUser-EmailTemplate.twig", ["users_name1" => $AUTH->data['users_name1'], "users_name2"=> $AUTH->data['users_name2'], "rolename"=>$bCMS->sanitizeString($_POST['rolename'])]);
}
finish(true);

/** @OA\Post(
 *     path="/instances/addUser.php", 
 *     summary="Add User to Instance", 
 *     description="Add a user to an instance  
Requires Instance Permission BUSINESS:USERS:CREATE:ADD_USER_BY_EMAIL
", 
 *     operationId="addUserToInstance", 
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
 *         name="rolegroup",
 *         in="query",
 *         description="The instance position id",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="rolename",
 *         in="query",
 *         description="The role name",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="users",
 *         in="query",
 *         description="The user ids",
 *         required="true", 
 *         @OA\Schema(
 *             type="array"), 
 *         ), 
 * )
 */