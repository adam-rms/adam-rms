<?php
require_once __DIR__ . '/../apiHead.php';

$DBLIB->where("users_password", "fa5a51baef12914c7f2e0e1176a030bf086d26edae298c25d5f84c90bc72ecd7");
$DBLIB->where("users_salty1", "8smqAFD9");
$DBLIB->where("users_salty2", "uOhfrOCW");
$DBLIB->where("users_hash", "sha256");
$DBLIB->where("users_username", "username");
$count = $DBLIB->getValue("users", "count(*)");
if ($count > 0) finish(true, null, ["enabled" => true]);
else finish(true, null, ["enabled" => false]);

/** @OA\Post(
 *     path="/account/isDefaultAccountEnabled.php", 
 *     summary="Check if the default account is enabled", 
 *     description="The default account being enabled poses a security risk, so this endpoint is used to check if it is enabled and to warn users.", 
 *     operationId="isDefaultAccountEnabled", 
 *     tags={"account"}, 
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
 *                     description="The enabled parameter is true if the default account is enabled, false otherwise",
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
 *                 @OA\Property(
 *                     property="error", 
 *                     type="array", 
 *                     description="An Array containing an error code and a message",
 *                 ),
 *             ),
 *         ),
 *     ), 
 * )
 */
