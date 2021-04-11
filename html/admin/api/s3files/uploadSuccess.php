<?php
require_once __DIR__ . '/../apiHeadSecure.php';

$fileData = [
    "s3files_extension" => pathinfo($bCMS->sanitizeString($_POST['name']), PATHINFO_EXTENSION),
    "s3files_path" => pathinfo($bCMS->sanitizeString($_POST['name']), PATHINFO_DIRNAME),
    "s3files_region" => $CONFIG['AWS']['DEFAULTUPLOADS']['REGION'],
    "s3files_endpoint" => $CONFIG['AWS']['DEFAULTUPLOADS']['ENDPOINT'],
    "s3files_bucket" => $CONFIG['AWS']['DEFAULTUPLOADS']['BUCKET'],
    "s3files_meta_size" => $bCMS->sanitizeString($_POST['size']),
    "s3files_meta_type" => $bCMS->sanitizeString($_POST['typeid']),
    "s3files_meta_subType" => is_numeric($_POST['subtype']) ? $bCMS->sanitizeString($_POST['subtype']) : null,
    "users_userid" => $AUTH->data['users_userid'],
    "s3files_original_name" => $bCMS->sanitizeString($_POST['originalName']),
    "s3files_filename" => preg_replace('/\\.[^.\\s]{3,4}$/', '', pathinfo($bCMS->sanitizeString($_POST['name']), PATHINFO_BASENAME)),
    "s3files_name" => pathinfo($bCMS->sanitizeString($_POST['originalName']), PATHINFO_FILENAME),
    "s3files_cdn_endpoint" => $CONFIG['AWS']['DEFAULTUPLOADS']['CDNEndpoint'],
    "s3files_meta_public" => $bCMS->sanitizeString($_POST['public']),
    "instances_id" => $AUTH->data['instance']['instances_id']
];
$id = $DBLIB->insert("s3files",$fileData);
echo $DBLIB->getLastError();
if (!$id) finish(false, ["code" => null, "message" => "Error"]);
else finish(true, null, ["id" => $id, "resize" => false,"url" => $CONFIG['ROOTURL'] . '/api/file/?f=' . $id]);