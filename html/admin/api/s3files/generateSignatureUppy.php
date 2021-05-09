<?php
require_once __DIR__ . '/../apiHeadSecure.php';

$bucket = $CONFIG['AWS']['DEFAULTUPLOADS']['BUCKET'];
// Directory to place uploaded files in.
use Aws\S3\S3Client;
// Create the S3 client.
$s3 = new Aws\S3\S3Client([
    'version' => 'latest',
    'region' => $CONFIG['AWS']['DEFAULTUPLOADS']['REGION'],
    'endpoint' => "https://" . $CONFIG['AWS']['DEFAULTUPLOADS']['ENDPOINT'],
    'credentials' => array(
        'key' => $CONFIG['AWS']['KEY'],
        'secret' => $CONFIG['AWS']['SECRET'],
    )
]);

$filename = $_POST['filename'];
$contentType = $_POST['contentType'];

// Prepare a PutObject command.
$command = $s3->getCommand('putObject', [
    'Bucket' => $bucket,
    'Key' => "{$filename}",
    'ContentType' => $contentType,
    'Body' => '',
]);

$request = $s3->createPresignedRequest($command, '+5 minutes');

header('content-type: application/json');
echo json_encode([
    'method' => $request->getMethod(),
    'url' => (string)$request->getUri(),
    'fields' => ['name','typeid','subtype'],
    // Also set the content-type header on the request, to make sure that it is the same as the one we used to generate the signature.
    // Else, the browser picks a content-type as it sees fit.
    'headers' => [
        'content-type' => $contentType,
    ],
]);