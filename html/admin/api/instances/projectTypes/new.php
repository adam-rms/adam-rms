<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_TYPES:CREATE")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['projectsTypes_name']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$array['instances_id'] = $AUTH->data['instance']['instances_id'];
$insert = $DBLIB->insert("projectsTypes", $array);
if (!$insert) finish(false);

$bCMS->auditLog("INSERT", "projectsTypes", json_encode($array), $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/instances/projectTypes/new.php", 
 *     summary="Create Project Type", 
 *     description="Create a project type  
Requires Instance Permission PROJECTS:PROJECT_TYPES:CREATE
", 
 *     operationId="createProjectType", 
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