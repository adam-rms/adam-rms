<?php
require_once __DIR__ . '/../../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_CREW:EDIT:CREW_RECRUITMENT")) die("404");

$array = [];
$array['projectsVacantRoles_visibleToGroups'] = [];
$array['projectsVacantRoles_applicationVisibleToUsers'] = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;

    if ($item['name'] == 'projectsVacantRoles_visibleToGroups') array_push($array['projectsVacantRoles_visibleToGroups'],$item['value']);
    else if ($item['name'] == 'projectsVacantRoles_applicationVisibleToUsers') array_push($array['projectsVacantRoles_applicationVisibleToUsers'],$item['value']);
    else $array[$item['name']] = $item['value'];
}

$checkboxes = ['projectsVacantRoles_open','projectsVacantRoles_firstComeFirstServed','projectsVacantRoles_fileUploads','projectsVacantRoles_collectPhone','projectsVacantRoles_privateToPM','projectsVacantRoles_showPublic'];
foreach ($checkboxes as $checkbox) {
    if (isset($array[$checkbox]) and $array[$checkbox] == "on") $array[$checkbox] = 1;
    else $array[$checkbox] = 0;
}

if ($array['projectsVacantRoles_visibleToGroups'] == []){
    $array['projectsVacantRoles_visibleToGroups'] = null;
} else {
    $array['projectsVacantRoles_visibleToGroups'] = implode(",",$array['projectsVacantRoles_visibleToGroups']);
    //Visible to specified groups so can't be public
    $array['projectsVacantRoles_showPublic'] = 0;
} 

if ($array['projectsVacantRoles_applicationVisibleToUsers'] == []) $array['projectsVacantRoles_applicationVisibleToUsers'] = null;
else $array['projectsVacantRoles_applicationVisibleToUsers'] = implode(",",$array['projectsVacantRoles_applicationVisibleToUsers']);

if ($array['projectsVacantRoles_deadline']) $array['projectsVacantRoles_deadline'] = date("Y-m-d H:i:s",strtotime($array['projectsVacantRoles_deadline']));

if (strlen($array['projectsVacantRoles_id']) <1 and $array['projectsVacantRoles_id'] != "NEW") finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects_id", $array['projects_id']);
$project = $DBLIB->getone("projects",["projects_id","projects_manager"]);
if (!$project) finish(false, ["code" => "PARAM-ERROR", "message"=> "No project for action"]);

if ($array['projectsVacantRoles_id'] == "NEW") {
    $array['projectsVacantRoles_id'] = null;
    $array['projectsVacantRoles_added'] = date("Y-m-d H:i:s");
    $insert = $DBLIB->insert("projectsVacantRoles", $array);
    if (!$insert) finish(false);
    $bCMS->auditLog("INSERT", "projectsVacantRoles", json_encode($array), $AUTH->data['users_userid']);
    finish(true);
} else {
    unset($array['projects_id']); //Don't allow them to change project for a role
    if ($project['projects_manager'] != $AUTH->data["users_userid"]) {
        // Only the PM can edit these settings
        unset($array['projectsVacantRoles_visibleToGroups']);
        unset($array['projectsVacantRoles_privateToPM']);
    }
 
    $DBLIB->where("projectsVacantRoles_deleted", 0);
    $DBLIB->where("projectsVacantRoles_id",$array['projectsVacantRoles_id']);
    $update = $DBLIB->update("projectsVacantRoles", $array,1);
    if (!$update) finish(false);

    $bCMS->auditLog("UPDATE", "projectsVacantRoles", json_encode($array), $AUTH->data['users_userid']);
    finish(true);
}

/** @OA\Post(
 *     path="/projects/crew/crewRoles/edit.php", 
 *     summary="Edit Vacant Role", 
 *     description="Edit a vacant role  
Requires Instance Permission PROJECTS:PROJECT_CREW:EDIT:CREW_RECRUITMENT
", 
 *     operationId="editVacantRole", 
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
 *         name="formData",
 *         in="query",
 *         description="Form Data",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="projectsVacantRoles_id", 
 *                 type="number", 
 *                 description="undefined",
 *             ),
 *         ),
 *     ), 
 * )
 */