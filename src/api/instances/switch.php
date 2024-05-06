<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (isset($_POST['instances_id'])) {
    finish($GLOBALS['AUTH']->setInstance($_POST['instances_id']), null, null);
}

finish(false, ["code" => "PARAM-ERROR", "message" => "Provide an instance id"], null);

/** @OA\Post(
 *     path="/instances/switch.php", 
 *     summary="Switch Instance", 
 *     description="Switch the active instance for the user
", 
 *     operationId="switchInstance", 
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
 *         name="instanceid",
 *         in="query",
 *         description="The instance id",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */