<?php
require_once __DIR__ . '/../apiHeadSecure.php';
$return = [];
foreach ($AUTH->data['instances'] as $instance) {
    $return[] = [
        "this" => ($AUTH->data['instance']['instances_id'] == $instance['instances_id']),
        "instances_name" =>  $instance['instances_name'],
        "permissions" => $instance['permissions'],
        "instances_id" => $instance['instances_id'],
    ];
}
finish(true, null, $return);
/** @OA\Get(
 *     path="/instances/list.php", 
 *     summary="List User Instances", 
 *     description="List all instances a user is a member of", 
 *     operationId="listUserInstances", 
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
 *                     description="An array of instances",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     )
 */