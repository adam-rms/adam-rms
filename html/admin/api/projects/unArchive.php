<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:ARCHIVE") or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("(projects.projects_id = ? OR projects.projects_parent_project_id = ?)", [$_POST['projects_id'],$_POST['projects_id']]);
$project = $DBLIB->update("projects", ["projects.projects_archived" => 0]);
if (!$project) finish(false);

$bCMS->auditLog("UNARCHIVE", "projects", "Removed the project and its subprojects from the archive", $AUTH->data['users_userid'],null, $_POST['projects_id']);

finish(true);

/** @OA\Post(
 *     path="/projects/unArchive.php", 
 *     summary="Unarchive", 
 *     description="Unarchive a project  
Requires Instance Permission PROJECTS:ARCHIVE
", 
 *     operationId="unArchive", 
 *     tags={"projects"}, 
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
 *         response="404", 
 *         description="Permission Error",
 *     ), 
 *     @OA\Parameter(
 *         name="projects_id",
 *         in="query",
 *         description="Project ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */