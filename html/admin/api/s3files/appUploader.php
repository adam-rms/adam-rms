<?php
//Very similar code in uploader for PDF invoices from projects
require_once __DIR__ . '/../apiHeadSecure.php';
if(isset($_FILES['file'])) {
    $temp_file_location = $_FILES['file']['tmp_name'];
    $s3 = new Aws\S3\S3Client([
        'version' => 'latest',
        'region' => $CONFIG['AWS']['DEFAULTUPLOADS']['REGION'],
        'endpoint' => "https://" . $CONFIG['AWS']['DEFAULTUPLOADS']['ENDPOINT'],
        'credentials' => array(
            'key' => $CONFIG['AWS']['KEY'],
            'secret' => $CONFIG['AWS']['SECRET'],
        )
    ]);
    $extension = pathinfo($_POST['filename'], PATHINFO_EXTENSION);
    $filename = "uploads/" . $_POST['typename'] . "/" . time() . "-" . (floor(rand())) . "." . $extension;
    $result = $s3->putObject([
        'Bucket' => $CONFIG['AWS']['DEFAULTUPLOADS']['BUCKET'],
        'Key'    => $filename,
        'SourceFile' => $temp_file_location
    ]);
    $code = $result['@metadata']['statusCode'];
    $uri = $result['@metadata']['effectiveUri'];
    if ($code === 200) {
        $fileData = [
            "s3files_extension" => pathinfo($filename, PATHINFO_EXTENSION),
            "s3files_path" => pathinfo($filename, PATHINFO_DIRNAME),
            "s3files_region" => $CONFIG['AWS']['DEFAULTUPLOADS']['REGION'],
            "s3files_endpoint" => $CONFIG['AWS']['DEFAULTUPLOADS']['ENDPOINT'],
            "s3files_bucket" => $CONFIG['AWS']['DEFAULTUPLOADS']['BUCKET'],
            "s3files_meta_size" => $_FILES['file']['size'],
            "s3files_meta_type" => $_POST['typeid'],
            "s3files_meta_subType" => is_numeric($_POST['subtype']) ? $bCMS->sanitizeString($_POST['subtype']) : null,
            "users_userid" => $AUTH->data['users_userid'],
            "s3files_original_name" => $bCMS->sanitizeString($_POST['filename']),
            "s3files_filename" => pathinfo($bCMS->sanitizeString($filename), PATHINFO_FILENAME),
            "s3files_name" => pathinfo($bCMS->sanitizeString($_POST['filename']), PATHINFO_FILENAME),
            "s3files_cdn_endpoint" => $CONFIG['AWS']['DEFAULTUPLOADS']['CDNEndpoint'],
            "s3files_meta_public" => $bCMS->sanitizeString($_POST['public']),
            "instances_id" => $AUTH->data['instance']['instances_id']
        ];
        $id = $DBLIB->insert("s3files",$fileData);
        echo $DBLIB->getLastError();
        if (!$id) finish(false, ["code" => null, "message" => "Error"]);
        else finish(true, null, ["id" => $id, "resize" => false,"url" => $CONFIG['ROOTURL'] . '/api/file/?f=' . $id]);
    } else finish(false, ["code" => null, "message" => "S3 Upload Error"]);
}

/** @OA\Post(
 *     path="/s3files/appUploader.php", 
 *     summary="App Uploader", 
 *     description="Upload a file to S3
", 
 *     operationId="appUploader", 
 *     tags={"s3files"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *     ), 
 *     @OA\Parameter(
 *         name="filename",
 *         in="query",
 *         description="File Name",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="typename",
 *         in="query",
 *         description="File Type",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="typeid",
 *         in="query",
 *         description="File Type ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="subtype",
 *         in="query",
 *         description="File Subtype",
 *         required="false", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="public",
 *         in="query",
 *         description="Public File",
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
 * )
 */