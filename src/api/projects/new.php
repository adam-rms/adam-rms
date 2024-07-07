<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:CREATE") or !isset($_POST['projects_name'])) die("404");
if (!$bCMS->instanceHasProjectCapacity($AUTH->data['instance']['instances_id'])) finish(false, ["code" => "PROJECT-LIMIT-REACHED", "message" => "You have reached the project limit for your business. Please upgrade your plan."]);

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projectsStatuses_deleted", 0);
$DBLIB->orderBy("projectsStatuses_rank", "ASC");
$projectsStatus = $DBLIB->getValue("projectsStatuses","projectsStatuses_id",1);

$hasProjectDates = isset($_POST['projects_dates_use_start']) and isset($_POST['projects_dates_use_end']);

$project = $DBLIB->insert("projects", [
    "projects_name" => $_POST['projects_name'],
    "instances_id" => $AUTH->data['instance']['instances_id'],
    "projects_description" => $_POST['projects_description'] ?? null,
    "projects_created" => date('Y-m-d H:i:s'),
    "projects_manager" => $_POST['projects_manager'],
    "projectsTypes_id" => $_POST['projectsType_id'],
    "projectsStatuses_id" => $projectsStatus,
    "projects_parent_project_id" => ($_POST['projects_parent_project_id'] ?? null),
    "projects_dates_use_start" => $hasProjectDates ? date("Y-m-d H:i:s", strtotime($_POST['projects_dates_use_start'])) : null,
    "projects_dates_use_end" => $hasProjectDates ? date("Y-m-d H:i:s", strtotime($_POST['projects_dates_use_end'])) : null
]);
if (!$project) finish(false, ["code" => "CREATE-PROJECT-FAIL", "message"=> "Could not create new project"]);

$bCMS->auditLog("INSERT", "projects",null, $AUTH->data['users_userid'],null, $project);
$bCMS->auditLog("UPDATE-NAME", "projects", "Set the name to ". $_POST['projects_name'], $AUTH->data['users_userid'],null, $project);
finish(true, null, ["projects_id" => $project]);

/** @OA\Post(
 *     path="/projects/new.php", 
 *     summary="New", 
 *     description="Create a new project  
Requires Instance Permission PROJECTS:CREATE
", 
 *     operationId="new", 
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
 *         name="projects_name",
 *         in="query",
 *         description="Project Name",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="projects_manager",
 *         in="query",
 *         description="Project Manager User ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="projectsType_id",
 *         in="query",
 *         description="Project Type ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="projects_parent_project_id",
 *         in="query",
 *         description="Parent Project ID",
 *         required="false", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="projects_dates_use_start",
 *         in="query",
 *         description="Project Start date/time, both this and projects_dates_use_end required to set this property",
 *         required="false", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="projects_dates_use_end",
 *         in="query",
 *         description="Project End date/time, both this and projects_dates_use_start required to set this property",
 *         required="false", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */