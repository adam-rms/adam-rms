<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:EDIT:LEAD") or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("users_userid", $_POST['users_userid']);
$user = $DBLIB->getone("users",["users_userid", "users_name1", "users_name2"]);
if (!$user) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", ["projects.projects_manager" => $user['users_userid']]);
if (!$project) finish(false);

$bCMS->auditLog("CHANGE-MANAGER", "projects", "Set the project manager to ". $user['users_name1'] . " " . $user['users_name2'], $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true);

/** @OA\Post(
 *     path="/projects/changeProjectManager.php", 
 *     summary="Change Project Manager", 
 *     description="Change the project manager of a project  
Requires Instance Permission PROJECTS:EDIT:LEAD
", 
 *     operationId="changeProjectManager", 
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
 *         name="users_userid",
 *         in="query",
 *         description="User ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */