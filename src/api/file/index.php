<?php
require_once __DIR__ . '/../apiHead.php';
/*
 * File interface for Amazon AWS S3.
 *  Parameters
 *      f (required) - the file id as specified in the database
 *      d (optional, default false) - should a download be forced or should it be displayed in the browser? (if set it will download)
 *      r (optional, default false) - should the url be returned by the script as plain text or a redirect triggered? (if set it will redirect)
 *      e (optional, default 1 minute) - when should the link expire? Must be a string describing how long in words basically. If this file type has security features then it will default to 1 minute.
 */
$file = $bCMS->s3URL($_POST['f'], (isset($_POST['d'])),(isset($_POST['e']) ? $_POST['e'] : null),(isset($_POST['key']) ? $_POST['key'] : null));
if (!$file) finish(false,["message"=>"File not found - please check to ensure you are still logged in"]);
else {
    if (isset($_POST['r'])) {
        header("Location: " . $file);
        die();
    } else finish(true,null,["url" => $file]);
}

/** @OA\Post(
 *     path="/file/index.php", 
 *     summary="Get File", 
 *     description="Get a file
", 
 *     operationId="getFile", 
 *     tags={"file_uploads"}, 
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
 *             ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="308", 
 *         description="Success - Redirect to this address",
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
 *         name="f",
 *         in="query",
 *         description="The file id",
 *         required="true", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 *     @OA\Parameter(
 *         name="d",
 *         in="query",
 *         description="should a download be forced or should it be displayed in the browser? (if set it will download)",
 *         required="false", 
 *         @OA\Schema(
 *             type="boolean"), 
 *         ), 
 *     @OA\Parameter(
 *         name="r",
 *         in="query",
 *         description="should the url be returned by the script as plain text or a redirect triggered? (if set it will redirect)",
 *         required="false", 
 *         @OA\Schema(
 *             type="boolean"), 
 *         ), 
 *     @OA\Parameter(
 *         name="e",
 *         in="query",
 *         description="when should the link expire? Must be a string describing how long in words basically. If this file type has security features then it will default to 1 minute.",
 *         required="false", 
 *         @OA\Schema(
 *             type="boolean"), 
 *         ), 
 * )
 */