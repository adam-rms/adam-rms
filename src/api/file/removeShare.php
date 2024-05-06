<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("FILES:FILE_ATTACHMENTS:EDIT:SHARING_SETTINGS") or !isset($_POST['s3files_id'])) die("404");


$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("s3files_id", $_POST['s3files_id']);
$update = $DBLIB->update("s3files", ["s3files_shareKey" => null],1); //Remove a share key
if (!$update) finish(false);

$bCMS->auditLog("UNSHARE-FILE", "s3files", null, $AUTH->data['users_userid'],null, $_POST['s3files_id']);
finish(true);

/** @OA\Post(
 *     path="/file/removeShare.php", 
 *     summary="Remove File Share", 
 *     description="Remove a file share  
Requires Instance Permission FILES:FILE_ATTACHMENTS:EDIT:SHARING_SETTINGS
", 
 *     operationId="removeFileShare", 
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
 *                 @OA\Property(
 *                     property="response", 
 *                     type="array", 
 *                     description="A null Array",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="404", 
 *         description="Error",
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
 *                     description="A null array",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="s3files_id",
 *         in="query",
 *         description="The file id",
 *         required="true", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 * )
 */