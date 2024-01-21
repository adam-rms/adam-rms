<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (!is_numeric($array['projectsTypes_id'])) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
elseif (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_TYPES:EDIT")) die("404");

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projectsTypes_deleted", 0);
$DBLIB->where("projectsTypes_id", $array['projectsTypes_id']);
$category = $DBLIB->update("projectsTypes", $array);
if (!$category) finish(false);

$bCMS->auditLog("EDIT", "projectsTypes", json_encode($array), $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/instances/projectTypes/edit.php", 
 *     summary="Edit Project Type", 
 *     description="Edit a project type  
Requires Instance Permission PROJECTS:PROJECT_TYPES:EDIT
", 
 *     operationId="editProjectType", 
 *     tags={"projectTypes"}, 
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
 *                 @OA\Property(
 *                     property="response", 
 *                     type="array", 
 *                     description="A null Array",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="default", 
 *         description="Error",
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
 *     @OA\Parameter(
 *         name="formData",
 *         in="query",
 *         description="The project type data",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="projectsTypes_id", 
 *                 type="integer", 
 *                 description="The project type id",
 *             ),
 *             @OA\Property(
 *                 property="projectTypes_name", 
 *                 type="string", 
 *                 description="The project type name",
 *             ),
 *             @OA\Property(
 *                 property="projectsTypes_config_finance", 
 *                 type="boolean", 
 *                 description="Use finance in project type",
 *             ),
 *             @OA\Property(
 *                 property="projectsTypes_config_files", 
 *                 type="boolean", 
 *                 description="Use files in project type",
 *             ),
 *             @OA\Property(
 *                 property="projectsTypes_config_assets", 
 *                 type="boolean", 
 *                 description="Use assets in project type",
 *             ),
 *             @OA\Property(
 *                 property="projectsTypes_config_client", 
 *                 type="boolean", 
 *                 description="Use clients in project type",
 *             ),
 *             @OA\Property(
 *                 property="projectsTypes_config_venue", 
 *                 type="boolean", 
 *                 description="Use locations in project type",
 *             ),
 *             @OA\Property(
 *                 property="projectsTypes_config_notes", 
 *                 type="boolean", 
 *                 description="Use notes in project type",
 *             ),
 *             @OA\Property(
 *                 property="projectsTypes_config_crew", 
 *                 type="boolean", 
 *                 description="Use crew in project type",
 *             ),
 *         ),
 *     ), 
 * )
 */