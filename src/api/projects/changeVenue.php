<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:EDIT:ADDRESS") or !isset($_POST['projects_id'])) die("404");

if ($_POST['locations_id'] != "") {
    $DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("locations.locations_id", $_POST['locations_id']);
    $DBLIB->where("locations.locations_deleted", 0);
    $DBLIB->where("locations.locations_archived", 0);
    $location = $DBLIB->getone("locations",["locations.locations_id"]);
    if (!$location) finish(false);
    else $locationid = $location['locations_id'];
} else $locationid = null;

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", ["projects.locations_id" => $locationid]);
if (!$project) finish(false);

$bCMS->auditLog("UPDATE-LOCATION", "projects", $locationid, $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true);

/** @OA\Post(
 *     path="/projects/changeVenue.php", 
 *     summary="Change Venue", 
 *     description="Change the venue of a project  
Requires Instance Permission PROJECTS:EDIT:ADDRESS
", 
 *     operationId="changeVenue", 
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
 *         name="locations_id",
 *         in="query",
 *         description="Location ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */