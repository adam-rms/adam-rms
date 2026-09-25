<?php
require_once __DIR__ . '/../apiHeadSecure.php';
if ($CONFIG['FILES_ENABLED'] !== "Enabled") {
    finish(false, ["code" => null, "message" => "File uploads are disabled"]);
}
//Files are listed by type and subtype (the record they're attached to) whatever business uploaded them, so the record must be this business's
$fileRecords = [ // type => [table, id column, SQL limiting the table to the business]
    3 => ["assetTypes", "assetTypes_id", "assetTypes.instances_id = ?"],
    4 => ["assets", "assets_id", "assets.instances_id = ?"],
    7 => ["projects", "projects_id", "projects.instances_id = ?"],
    8 => ["maintenanceJobs", "maintenanceJobs_id", "maintenanceJobs.instances_id = ?"],
    11 => ["locations", "locations_id", "locations.instances_id = ?"],
    12 => ["modules", "modules_id", "modules.instances_id = ?"],
    13 => ["modulesSteps", "modulesSteps_id", "modulesSteps.modules_id IN (SELECT modules_id FROM modules WHERE instances_id = ?)"],
    14 => ["payments", "payments_id", "payments.projects_id IN (SELECT projects_id FROM projects WHERE instances_id = ?)"],
    18 => ["projectsVacantRoles", "projectsVacantRoles_id", "projectsVacantRoles.projects_id IN (SELECT projects_id FROM projects WHERE instances_id = ?)"],
    19 => ["cmsPages", "cmsPages_id", "cmsPages.instances_id = ?"],
];
if (isset($fileRecords[$_POST['typeid']]) and is_numeric($_POST['subtype'])) {
    [$table, $idColumn, $inBusiness] = $fileRecords[$_POST['typeid']];
    $DBLIB->where($idColumn, $_POST['subtype']);
    //Asset type thumbnails and attachments: server admins can edit any business's (and shared) types
    if ($_POST['typeid'] != 3 or !$AUTH->serverPermissionCheck("ASSETS:EDIT:ANY_ASSET_TYPE")) $DBLIB->where($inBusiness, [$AUTH->data['instance']['instances_id']]);
    if (!$DBLIB->getOne($table, [$idColumn])) finish(false, ["code" => null, "message" => "Record not found"]);
}
$fileData = [
    "s3files_extension" => pathinfo($bCMS->sanitizeString($_POST['name']), PATHINFO_EXTENSION),
    "s3files_path" => pathinfo($bCMS->sanitizeString($_POST['name']), PATHINFO_DIRNAME),
    "s3files_meta_size" => $bCMS->sanitizeString($_POST['size']),
    "s3files_meta_type" => $bCMS->sanitizeString($_POST['typeid']),
    "s3files_meta_subType" => is_numeric($_POST['subtype']) ? $bCMS->sanitizeString($_POST['subtype']) : null,
    "users_userid" => $AUTH->data['users_userid'],
    "s3files_original_name" => $bCMS->sanitizeString($_POST['originalName']),
    "s3files_filename" => pathinfo($bCMS->sanitizeString($_POST['name']), PATHINFO_FILENAME),
    "s3files_name" => pathinfo($bCMS->sanitizeString($_POST['originalName']), PATHINFO_FILENAME),
    "s3files_meta_public" => $bCMS->sanitizeString($_POST['public']),
    "instances_id" => $AUTH->data['instance']['instances_id']
];
$id = $DBLIB->insert("s3files",$fileData);
echo $DBLIB->getLastError();
if (!$id) finish(false, ["code" => null, "message" => "Error"]);
else finish(true, null, ["id" => $id, "resize" => false,"url" => $CONFIG['ROOTURL'] . '/api/file/?f=' . $id]);

/** @OA\Post(
 *     path="/s3files/uploadSuccess.php", 
 *     summary="Upload Success", 
 *     description="Upload a file to S3
", 
 *     operationId="uploadSuccess", 
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
 *         name="name",
 *         in="query",
 *         description="File Name",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="size",
 *         in="query",
 *         description="File Size",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
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
 *         name="orignalName",
 *         in="query",
 *         description="Original File Name",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="public",
 *         in="query",
 *         description="Public File",
 *         required="true", 
 *         @OA\Schema(
 *             type="boolean"), 
 *         ), 
 * )
 */