<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:CREATE") or !isset($_POST['projects_id'])) die("404");

if($_POST['projects_parent_project_id'] == -1) {
    $parent_id = null;
} else {
    $DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("projects.projects_id", $_POST['projects_parent_project_id']);
    $DBLIB->where("projects.projects_deleted", 0);
    $parent = $DBLIB->getOne("projects", ["projects.projects_id"]);
    if (!$parent) finish(false, ["code" => null, "message"=> "Error changing parent project"]);
    else $parent_id = $parent['projects_id'];
}

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", ["projects.projects_parent_project_id" => $parent_id]);
if (!$project) finish(false);

$bCMS->auditLog("UPDATE-SUBPROJECT", "projects", $parent_id, $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true);

/** @OA\Post(
 *     path="/projects/changeSubProject.php", 
 *     summary="Change Sub Project", 
 *     description="Change the parent project of a project  
Requires Instance Permission PROJECTS:CREATE
", 
 *     operationId="changeSubProject", 
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
 *         name="projects_parent_project_id",
 *         in="query",
 *         description="Parent Project ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */