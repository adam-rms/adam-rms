<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:EDIT:DATES") or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", ["projects.projects_dates_use_start" => date ("Y-m-d H:i:s", strtotime($_POST['projects_dates_use_start'])), "projects.projects_dates_use_end" => date ("Y-m-d H:i:s", strtotime($_POST['projects_dates_use_end']))]);
if (!$project) finish(false);

$bCMS->auditLog("CHANGE-DATE", "projects", "Set the start date to ". date ("D jS M Y h:i:sa", strtotime($_POST['projects_dates_use_start'])) . "\nSet the end date to ". date ("D jS M Y h:i:sa", strtotime($_POST['projects_dates_use_end'])), $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true);

/** @OA\Post(
 *     path="/projects/changeProjectDates.php", 
 *     summary="Change Project Dates", 
 *     description="Change the start and end dates of a project  
Requires Instance Permission PROJECTS:EDIT:DATES
", 
 *     operationId="changeProjectDates", 
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
 *         name="projects_dates_use_start",
 *         in="query",
 *         description="Start Date/Time",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="projects_dates_use_end",
 *         in="query",
 *         description="End Date/Time",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */