<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("FILES:FILE_ATTACHMENTS:EDIT:SHARING_SETTINGS") or !isset($_POST['s3files_id'])) die("404");


$shareKey = $bCMS->randomString(40);

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("s3files_id", $_POST['s3files_id']);
$update = $DBLIB->update("s3files", ["s3files_shareKey" => $shareKey],1); //Add a share key
if (!$update) finish(false);

$bCMS->auditLog("SHARE-FILE", "s3files", null, $AUTH->data['users_userid'],null, $_POST['s3files_id']);
finish(true,null,["s3files_shareKey" => hash('sha256', $shareKey . "|" . $_POST['s3files_id'])]);

/**
 *  @OA\Post(
 *      path="/file/share.php",
 *      summary="Share file",
 *      description="Make a file publicly accessible, by generating a share key",
 *      operationId="shareFile",
 *      tags={"file_uploads"},
 *      @OA\Response(
 *          response="200",
 *          description="Success",
 *          @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *      ),
 *      @OA\Parameter(
 *          name="s3files_id",
 *          in="query",
 *          description="Id of the file to share",
 *          required="true",
 *          @OA\Schema(
 *              type="number",
 *          ),
 *      ),
 *  )
 */