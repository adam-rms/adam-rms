<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->serverPermissionCheck("CONFIG:SET") or !isset($_POST['property_key']) or !isset($_POST['property_value'])) die("404");

if ($CONFIGCLASS->updateSingleProperty($_POST['property_key'], $_POST['property_value'])) {
  finish(true);
} else finish(false);

/** @OA\Post(
 *     path="/server/configPropertyValue.php", 
 *     summary="Update Config value", 
 *     description="Update a single property value. Only specific keys are accepted", 
 *     operationId="updateConfigValue", 
 *     tags={"server"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="OK",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="property_key",
 *         in="query",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ),
 *     @OA\Parameter(
 *         name="property_value",
 *         in="query",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
