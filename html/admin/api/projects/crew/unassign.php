<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_CREW:EDIT") or !isset($_POST['crewAssignments_id'])) die("404");

$DBLIB->where("crewAssignments_id", $_POST['crewAssignments_id']);
$DBLIB->where("crewAssignments_deleted", 0);
$DBLIB->join("projects", "crewAssignments.projects_id=projects.projects_id", "LEFT");
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$assignment = $DBLIB->getone("crewAssignments", ["crewAssignments.crewAssignments_id", "crewAssignments.users_userid", "projects.projects_id", "projects.projects_name","crewAssignments.crewAssignments_role"]);
if (!$assignment) finish(false);
else {
    $bCMS->auditLog("UNASSIGN-CREW", "crewAssignments", $assignment['crewAssignments_id'], $AUTH->data['users_userid'],null, $assignment['projects_id']);
    if ($assignment["users_userid"]) {
        notify(10, $assignment["users_userid"], $AUTH->data['instance']['instances_id'], $AUTH->data['users_name1'] . " " . $AUTH->data['users_name2'] . " removed you from the crew of the project " . $assignment['projects_name'] . " in the role of " . $assignment["crewAssignments_role"]);
    }
    $DBLIB->where("crewAssignments_id", $assignment['crewAssignments_id']);
    if ($DBLIB->update("crewAssignments", ["crewAssignments_deleted" => 1])) finish(true);
    else finish(false);
}

/** @OA\Post(
 *     path="/projects/crew/unassign.php", 
 *     summary="Unassign Crew", 
 *     description="Unassign crew from a project  
Requires Instance Permission PROJECTS:PROJECT_CREW:EDIT
", 
 *     operationId="unassignCrew", 
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
 *         name="crewAssignments_id",
 *         in="query",
 *         description="Crew Assignment ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */