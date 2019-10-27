<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$CONFIG['AWS']['UPLOAD']) die("Uploads disabled");

if (isset($_POST['qqparentuuid'])) {
    //This file is just a resize of an existing file - so we can basically ignore it
    header('Content-type: application/json');
    die("" . json_encode(["id" => null, "resize" => true]));
}
$fileData = [
    "s3files_extension" => pathinfo($bCMS->sanitizeString($_POST['key']), PATHINFO_EXTENSION),
    "s3files_path" => pathinfo($bCMS->sanitizeString($_POST['key']), PATHINFO_DIRNAME),
    "s3files_region" => $CONFIG['AWS']['DEFAULTUPLOADS']['REGION'],
    "s3files_endpoint" => $CONFIG['AWS']['DEFAULTUPLOADS']['ENDPOINT'],
    "s3files_meta_public" => $bCMS->sanitizeString($_POST['public']),
    "s3files_bucket" => $bCMS->sanitizeString($_POST['bucket']),
    "s3files_meta_size" => $bCMS->sanitizeString($_POST['size']),
    "s3files_meta_type" => $bCMS->sanitizeString($_POST['typeid']),
    "s3files_meta_subType" => $bCMS->sanitizeString($_POST['subtype']),
    "users_userid" => $AUTH->data['users_userid'],
    "s3files_original_name" => $bCMS->sanitizeString($_POST['name']),
    "s3files_filename" => preg_replace('/\\.[^.\\s]{3,4}$/', '', pathinfo($bCMS->sanitizeString($_POST['key']), PATHINFO_BASENAME)),
    "s3files_cdn_endpoint" => $CONFIG['AWS']['DEFAULTUPLOADS']['CDNEndpoint']
];
$id = $DBLIB->insert("s3files",$fileData);
if (!$id) {
    header("HTTP/1.1 500 Internal Server Error");
    die($DBLIB->getLastError());
} else {
    header('Content-type: application/json');
    die("" . json_encode(["id" => $id, "resize" => false]));
}
?>