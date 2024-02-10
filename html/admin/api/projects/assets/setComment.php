<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMNET_COMMENT") or !isset($_POST['assetsAssignments'])) die("404");
foreach ($_POST['assetsAssignments'] as $assignment) {
    $DBLIB->where("assetsAssignments_id", $assignment);
    $DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("projects.projects_deleted", 0);
    $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
    $assignment = $DBLIB->update("assetsAssignments", ["assetsAssignments_comment" => $_POST['assetsAssignments_comment']]);
    if (!$assignment) finish(false);
    else {
        $bCMS->auditLog("EDIT-COMMENT", "assetsAssignments", $_POST['assetsAssignments_comment'], $AUTH->data['users_userid'],null, $assignment['projects_id']);
    }
}
finish(true);

/** @OA\Post(
 *     path="/projects/assets/setComment.php", 
 *     summary="Set Asset Assignment Comment", 
 *     description="Set the comment for an asset assignment  
Requires Instance Permission PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMNET_COMMENT
", 
 *     operationId="setAssetAssignmentComment", 
 *     tags={"project_assets"}, 
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
 *         name="assetsAssignments",
 *         in="query",
 *         description="Asset Assignment IDs",
 *         required="true", 
 *         @OA\Schema(
 *             type="array"), 
 *         ), 
 *     @OA\Parameter(
 *         name="assetsAssignments_comment",
 *         in="query",
 *         description="Comment",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */