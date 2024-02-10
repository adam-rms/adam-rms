<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects.projects_id"]);
if (!$project) die("404");

$bCMS->auditLog("QUICKCOMMENT", "projects", $bCMS->cleanString($_POST['text']), $AUTH->data['users_userid'],null, $_POST['projects_id']);

finish(true, null, ["projects_id" => $project]);

/** @OA\Post(
 *     path="/projects/newQuickComment.php", 
 *     summary="New Quick Comment", 
 *     description="Create a new project quick comment  
Requires Instance Permission PROJECTS:VIEW
", 
 *     operationId="newQuickComment", 
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
 *         name="text",
 *         in="query",
 *         description="Comment Text",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */