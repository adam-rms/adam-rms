<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_CREW:CREATE")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
//Duplicated in application section for vacancies
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $array['projects_id']);
$DBLIB->join("users", "projects.projects_manager=users.users_userid", "LEFT");
$project = $DBLIB->getone("projects", ["projects_id","projects_name", "projects_dates_use_start","projects_dates_use_end", "users.users_name1", "users.users_name2", "users.users_email"]);
if (!$project) finish(false);

foreach ($_POST['users'] as $user) {
    $data = [
        "projects_id" => $project["projects_id"],
        "crewAssignments_comment" => $array["crewAssignments_comment"],
        "crewAssignments_role" => $array["crewAssignments_role"]
    ];
    if (is_numeric($user)) {
        //Find the user
        $DBLIB->where("users.users_userid", $user);
        $DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
        $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
        $DBLIB->where("instancePositions.instances_id",  $AUTH->data['instance']['instances_id']);
        $DBLIB->where("userInstances.userInstances_deleted",  0);
        $DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
        $usersql = $DBLIB->getone("users", ["users.users_userid", "users.users_name1"]);
        if (!$usersql) continue; //User not found - let's skip this for now
        else $data['users_userid'] = $usersql['users_userid'];
        //Search for clashes for that user - (duplicated in the clash checker system for the frontend)
        if ($project["projects_dates_use_start"] and $project["projects_dates_use_end"]) {
            $DBLIB->where("users_userid", $usersql['users_userid']);
            $DBLIB->where("crewAssignments.crewAssignments_deleted", 0);
            $DBLIB->join("projects", "crewAssignments.projects_id=projects.projects_id", "LEFT");
            $DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
            $DBLIB->where("projects.projects_deleted", 0);
            $DBLIB->where("(crewAssignments.projects_id != " . $project['projects_id'] . ")");
            $DBLIB->where("projectsStatuses.projectsStatuses_assetsReleased", 0);
            $DBLIB->where("((projects_dates_use_start >= '" . $project["projects_dates_use_start"] . "' AND projects_dates_use_start <= '" . $project["projects_dates_use_end"] . "') OR (projects_dates_use_end >= '" . $project["projects_dates_use_start"] . "' AND projects_dates_use_end <= '" . $project["projects_dates_use_end"] . "') OR (projects_dates_use_end >= '" . $project["projects_dates_use_end"] . "' AND projects_dates_use_start <= '" . $project["projects_dates_use_start"] . "'))");
            $existingAssignments = $DBLIB->get("crewAssignments", null, ["crewAssignments.projects_id", "projects.projects_name", "crewAssignments.crewAssignments_role"]);
        } else $existingAssignments = [];
        //Allow crew to be clashed - but it'll warn them in the notification.
    } else {
        $data['crewAssignments_personName'] = $user;
        $usersql = false;
    }
    $insert = $DBLIB->insert("crewAssignments", $data);
    if (!$insert) finish(false);
    else {
        $bCMS->auditLog("ASSIGN-CREW", "crewAssignments", $insert, $AUTH->data['users_userid'],null, $project['projects_id']);
        if ($usersql and ($usersql['users_userid'] != $AUTH->data['users_userid'])) { //Email if it's a real user, but don't email if they assigned themselves
            $data =
                [
                    "project" => $project,
                    "clashes" => $existingAssignments,
                    "assignment" => $data
                ];
            notify(11, $usersql['users_userid'], $AUTH->data['instance']['instances_id'], $AUTH->data['users_name1'] . " " . $AUTH->data['users_name2'] . " added you as " . $bCMS->sanitizeString($array["crewAssignments_role"]) . " for the project " . $project['projects_name'],false, "api/projects/crew/assign-EmailTemplate.twig", $data);
        }
    }
}
finish(true);

/** @OA\Post(
 *     path="/projects/crew/assign.php", 
 *     summary="Assign Crew", 
 *     description="Assign crew to a project  
Requires Instance Permission PROJECTS:PROJECT_CREW:CREATE
", 
 *     operationId="assignCrew", 
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
 *         name="formData",
 *         in="query",
 *         description="Form Data",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="projects_id", 
 *                 type="number", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="crewAssignments_comment", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="crewAssignments_role", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="users",
 *         in="query",
 *         description="Users",
 *         required="true", 
 *         @OA\Schema(
 *             type="array"), 
 *         ), 
 * )
 */