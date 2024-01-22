<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_NOTES:CREATE:NOTES")) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects_id"]);
if (!$project) finish(false);

$insert = $DBLIB->insert("projectsNotes", ["projects_id" => $project['projects_id'], "projectsNotes_userid" => $AUTH->data['users_userid'], "projectsNotes_title" => $_POST['projectsNotes_title']]);
if (!$insert) finish(false);

$bCMS->auditLog("INSERT", "projectsNotes", $insert, $AUTH->data['users_userid'],null, $project['projects_id']);
finish(true);

/** @OA\Post(
 *     path="/projects/newNote.php", 
 *     summary="New Note", 
 *     description="Create a new project note  
Requires Instance Permission PROJECTS:PROJECT_NOTES:CREATE:NOTES
", 
 *     operationId="newNote", 
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
 *         name="projectsNotes_title",
 *         in="query",
 *         description="Project Note Text",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */