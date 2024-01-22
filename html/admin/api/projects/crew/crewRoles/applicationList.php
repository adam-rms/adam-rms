<?php
require_once __DIR__ . '/../../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_CREW:EDIT:CREW_RECRUITMENT")) die(404);
if (!isset($_POST['projectsVacantRoles_id'])) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("projectsVacantRoles.projectsVacantRoles_deleted",0);
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->join("projects","projectsVacantRoles.projects_id=projects.projects_id","LEFT");
$DBLIB->where("projectsVacantRoles.projectsVacantRoles_id",$_POST['projectsVacantRoles_id']);
$role = $DBLIB->getOne("projectsVacantRoles");
if (!$role) finish(false, ["code" => "PARAM-ERROR", "message"=> "Invalid role ID"]);
elseif ($role['projects_manager'] != $AUTH->data["users_userid"] and $role['projectsVacantRoles_privateToPM'] == 1) finish(false, ["code" => "AUTH-ERROR", "message"=> "No permission to manage this role"]);

$DBLIB->where("projectsVacantRoles_id",$role["projectsVacantRoles_id"]);
$DBLIB->where("projectsVacantRolesApplications_deleted",0);
$DBLIB->join("users","projectsVacantRolesApplications.users_userid=users.users_userid","LEFT");
$DBLIB->orderBy("projectsVacantRolesApplications_submitted","ASC");
$applications = $DBLIB->get("projectsVacantRolesApplications", null, ["projectsVacantRolesApplications.*","users.users_userid", "users.users_name1", "users.users_name2", "users.users_email"]);
$formattedApplications = [];
foreach ($applications as $application) {
    $application['projectsVacantRolesApplications_questionAnswers'] = json_decode($application['projectsVacantRolesApplications_questionAnswers'],true);

    if ($application['projectsVacantRolesApplications_files'] != "") {
        $DBLIB->where("s3files_meta_type", 18);
        $DBLIB->where("s3files_id", explode(",",$application['projectsVacantRolesApplications_files']),"IN");
        $DBLIB->where("s3files_meta_subType", $role["projectsVacantRoles_id"]);
        $DBLIB->where("(s3files_meta_deleteOn >= '". date("Y-m-d H:i:s") . "' OR s3files_meta_deleteOn IS NULL)");
        $DBLIB->where("s3files_meta_physicallyStored",1);
        $DBLIB->orderBy("s3files_meta_uploaded", "ASC");
        $application['files'] = $DBLIB->get("s3files", null, ["s3files_id", "s3files_extension", "s3files_name","s3files_meta_size", "s3files_meta_uploaded"]);
    } else $application['files'] = [];


    $formattedApplications[] = $application;
}

finish(true, null, $formattedApplications);

/** @OA\Post(
 *     path="/projects/crew/crewRoles/applicationList.php", 
 *     summary="Get Vacant Role Application List", 
 *     description="Get the list of applications for a vacant role  
Requires Instance Permission PROJECTS:PROJECT_CREW:EDIT:CREW_RECRUITMENT
", 
 *     operationId="getVacantRoleApplicationList", 
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
 *         name="projectsVacantRoles_id",
 *         in="query",
 *         description="Vacant Role ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */