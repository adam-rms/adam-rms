<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_CREW:EDIT:CREW_RANKS")) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects_id","projects_name", "projects_dates_use_start","projects_dates_use_end"]);
if (!$project) finish(false);

foreach ($_POST['order'] as $count=>$item) {
    $DBLIB->where("crewAssignments.projects_id",$project['projects_id']);
    $DBLIB->where("crewAssignments.crewAssignments_id",$item);
    if (!$DBLIB->update("crewAssignments", ["crewAssignments_rank" => $count], 1)) finish(false);
}
$bCMS->auditLog("RANK-CREW", "crewAssignments", null, $AUTH->data['users_userid'],null, $project['projects_id']);
finish(true);

/** @OA\Post(
 *     path="/projects/crew/sortRank.php", 
 *     summary="Sort Crew", 
 *     description="Sort the crew of a project  
Requires Instance Permission PROJECTS:PROJECT_CREW:EDIT:CREW_RANKS
", 
 *     operationId="sortCrew", 
 *     tags={"crew"}, 
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
 *         name="order",
 *         in="query",
 *         description="Order",
 *         required="true", 
 *         @OA\Schema(
 *             type="array"), 
 *         ), 
 * )
 */