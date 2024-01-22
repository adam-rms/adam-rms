<?php
require_once __DIR__ . '/../../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_CREW:VIEW:VIEW_AND_APPLY_FOR_CREW_ROLES")) die("404");

$DBLIB->where("projectsVacantRoles.projectsVacantRoles_deleted",0);
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->join("projectsVacantRoles","projectsVacantRolesApplications.projectsVacantRoles_id=projectsVacantRoles.projectsVacantRoles_id","LEFT");
$DBLIB->join("projects","projectsVacantRoles.projects_id=projects.projects_id","LEFT");
$DBLIB->join("users","projectsVacantRolesApplications.users_userid=users.users_userid","LEFT");
$DBLIB->where("projectsVacantRolesApplications.projectsVacantRolesApplications_deleted",0);
$DBLIB->where("projectsVacantRolesApplications_id",$_POST["projectsVacantRolesApplications_id"]);
$application = $DBLIB->getOne("projectsVacantRolesApplications", null, ["projectsVacantRolesApplications_id","projects_name","projects.projects_id","projectsVacantRoles_name","users.users_userid","projectsVacantRoles_name","projectsVacantRoles_slotsFilled"]);
if (!$application) finish(false,["message"=>"Application not found"]);

$insert = $DBLIB->insert("crewAssignments", [
    "projects_id" => $application["projects_id"],
    "users_userid" => $application['users_userid'],
    "crewAssignments_role" => $application['projectsVacantRoles_name']
]);
if (!$insert) finish(false, ["message"=>"Could not assign this user to the role"]);

$bCMS->auditLog("ASSIGN-CREW", "crewAssignments", "Assign via vacancy application success", $AUTH->data['users_userid'],$application['users_userid'], $application['projects_id']);

$DBLIB->where("projectsVacantRoles_id",$application['projectsVacantRoles_id']);
$DBLIB->update("projectsVacantRoles",["projectsVacantRoles_slotsFilled"=>$application['projectsVacantRoles_slotsFilled']+1],1);

$DBLIB->where("projectsVacantRolesApplications_id",$application["projectsVacantRolesApplications_id"]);
$DBLIB->update("projectsVacantRolesApplications",["projectsVacantRolesApplications_status"=>1]);

notify(41,$application['users_userid'], $AUTH->data['instance']['instances_id'], "Application successful for " . $application['projects_name'], "Congratulations, you've been allocated the role of " . $application['projectsVacantRoles_name'] . " on " . $application['projects_name'] . ".<br/><br/>If you have any queries please contact " . $AUTH->data['users_name1'] . " " .  $AUTH->data['users_name2'] . " (" . $AUTH->data['users_email'] . ")");

finish(true);

/** @OA\Post(
 *     path="/projects/crew/crewRoles/accept.php", 
 *     summary="Accept Vacant Role Application", 
 *     description="Accept a vacant role application  
Requires Instance Permission PROJECTS:PROJECT_CREW:VIEW:VIEW_AND_APPLY_FOR_CREW_ROLES
", 
 *     operationId="acceptVacantRoleApplication", 
 *     tags={"recruitment"}, 
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
 *         name="projectsVacantRolesApplications_id",
 *         in="query",
 *         description="Vacant Role Application ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */