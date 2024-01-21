<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:EDIT:PROJECT_TYPE") or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projectsTypes_id",$_POST['projectsTypes_id']);
$DBLIB->where("projectsTypes_deleted", 0);
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$type = $DBLIB->getOne("projectsTypes",["projectsTypes_id","projectsTypes_name"]);
if (!$type) finish(false);


$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", ["projects.projectsTypes_id" => $type['projectsTypes_id']]);
if (!$project) finish(false);

$bCMS->auditLog("CHANGE-TYPE", "projects", "Set the project type to ". $type['projectsTypes_name'], $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true);

/** @OA\Post(
 *     path="/projects/changeProjectType.php", 
 *     summary="Change Project Type", 
 *     description="Change the project type of a project  
Requires Instance Permission PROJECTS:EDIT:PROJECT_TYPE
", 
 *     operationId="changeProjectType", 
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
 *     @OA\Parameter(
 *         name="projectsTypes_id",
 *         in="query",
 *         description="Project Type id",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */