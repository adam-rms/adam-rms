<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_NOTES:EDIT:NOTES") or !isset($_POST['projectsNotes_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects");
if (!$project) finish(false);

$DBLIB->where("projectsNotes_id", $_POST['projectsNotes_id']);
$DBLIB->where("projectsNotes_deleted", 0);
$DBLIB->where("projects_id", $project['projects_id']);
$note = $DBLIB->getone("projectsNotes", ["projectsNotes_title"]);
if (!$note) finish(false);

$DBLIB->where("projectsNotes_id", $_POST['projectsNotes_id']);
$update = $DBLIB->update("projectsNotes", ["projectsNotes_text" => $bCMS->cleanString($_POST['projectsNotes_text'])]);
if (!$update) finish(false);

$bCMS->auditLog("UPDATE-PROJECTNOTETEXT", "projects", "Updated the notes for ". $note['projectsNotes_title'] . " to ". $_POST['projectsNotes_text'], $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true);

/** @OA\Post(
 *     path="/projects/editNote.php", 
 *     summary="Edit Note", 
 *     description="Edit a project note  
Requires Instance Permission PROJECTS:PROJECT_NOTES:EDIT:NOTES
", 
 *     operationId="editNote", 
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
 *         name="projectsNotes_id",
 *         in="query",
 *         description="Project Note Id",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="projectsNotes_text",
 *         in="query",
 *         description="Project Note Text",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */