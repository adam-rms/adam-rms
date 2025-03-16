<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:EDIT:DELIVERY_NOTES") or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", ["projects.projects_deliveryNotes" => $_POST['projects_deliveryNotes']]);
if (!$project) finish(false);

$bCMS->auditLog("UPDATE-deliveryNotes", "projects", "Set the delivery Notes to " . $_POST['projects_deliveryNotes'], $AUTH->data['users_userid'], null, $_POST['projects_id']);
finish(true);

/** @OA\Post(
 *     path="/projects/changedeliveryNotes.php", 
 *     summary="Change delivery Notes", 
 *     description="Change the delivery Notes of a project  
Requires Instance Permission PROJECTS:EDIT:DELIVERY_NOTES
", 
 *     operationId="changedeliveryNotes", 
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
 *         name="projects_deliveryNotes",
 *         in="query",
 *         description="delivery Notes",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
