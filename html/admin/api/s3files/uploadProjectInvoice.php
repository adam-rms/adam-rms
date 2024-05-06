<?php
require_once __DIR__ . '/../apiHeadSecure.php';
if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") or !isset($_POST['id']) or $CONFIG['FILES_ENABLED'] !== "Enabled") finish(false);

if (isset($_FILES['file'])) {
    $temp_file_location = $_FILES['file']['tmp_name'];
    $s3 = new Aws\S3\S3Client([
        'version' => 'latest',
        'region' => $CONFIGCLASS->get('AWS_S3_REGION'),
        'endpoint' => $CONFIGCLASS->get('AWS_S3_SERVER_ENDPOINT'),
        'use_path_style_endpoint' => $CONFIGCLASS->get('AWS_S3_ENDPOINT_PATHSTYLE') === 'Enabled',
        'credentials' => array(
            'key' => $CONFIGCLASS->get('AWS_S3_KEY'),
            'secret' => $CONFIGCLASS->get('AWS_S3_SECRET'),
        )
    ]);
    $isQuote = $_POST['quote'] == "true";
    $filename = sprintf("%s-", $isQuote ? "quote" : "invoice") . time() . "-" . (floor(rand())) . "." . "pdf";
    $s3Path = $isQuote ? "uploads/PROJECT_QUOTES" : "uploads/PROJECT_INVOICES";
    $result = $s3->putObject([
        'Bucket' => $CONFIGCLASS->get('AWS_S3_BUCKET'),
        'Key'    => $s3Path . "/" . $filename,
        'SourceFile' => $temp_file_location
    ]);
    $code = $result['@metadata']['statusCode'];
    $uri = $result['@metadata']['effectiveUri'];
    if ($code === 200) {
        $fileData = [
            "s3files_extension" => "pdf",
            "s3files_path" => $s3Path,
            "s3files_meta_size" => $_FILES['file']['size'],
            "s3files_meta_type" => $isQuote ? 21 : 20,
            "s3files_meta_subType" => $_POST['id'],
            "users_userid" => $AUTH->data['users_userid'],
            "s3files_original_name" => $isQuote ? "quote.pdf" : "invoice.pdf",
            "s3files_filename" => pathinfo($filename, PATHINFO_FILENAME),
            "s3files_name" => "v" . $bCMS->sanitizeString($_POST['fileNumber']),
            "s3files_meta_public" => 0,
            "instances_id" => $AUTH->data['instance']['instances_id']
        ];
        $id = $DBLIB->insert("s3files", $fileData);
        echo $DBLIB->getLastError();
        if (!$id) finish(false, ["code" => null, "message" => "Error"]);
        else finish(true, null, ["id" => $id, "resize" => false, "url" => $CONFIG['ROOTURL'] . '/api/file/?r=true&f=' . $id]);
    } else finish(false, ["code" => null, "message" => "S3 Upload Error"]);
}
/** @OA\Post(
 *     path="/s3files/uploadProjectInvoice.php", 
 *     summary="Upload Project Invoice", 
 *     description="Upload a project invoice  
Requires Instance Permission PROJECTS:VIEW
", 
 *     operationId="uploadProjectInvoice", 
 *     tags={"s3files"}, 
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
 *     @OA\Parameter(
 *         name="id",
 *         in="query",
 *         description="Project ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="quote",
 *         in="query",
 *         description="Is Quote?",
 *         required="true", 
 *         @OA\Schema(
 *             type="boolean"), 
 *         ), 
 *     @OA\Parameter(
 *         name="file",
 *         in="files",
 *         description="File",
 *         required="true", 
 *         ), 
 *     @OA\Parameter(
 *         name="fileNumber",
 *         in="query",
 *         description="File Version Number",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */
