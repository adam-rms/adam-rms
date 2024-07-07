<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->serverPermissionCheck("INSTANCES:PERMANENTLY_DELETE") or !isset($_POST['instances_id'])) die("404");

$DBLIB->where('instances_id', $_POST['instances_id']);
$DBLIB->where("instances_deleted", 1);
if (!$DBLIB->getOne("instances", "instances_id")) finish(false);

$DBLIB->where('instances_id', $_POST['instances_id']);
$DBLIB->where("instances_deleted", 1);
if($DBLIB->delete('instances')) {
    $bCMS->auditLog("DELETE-INSTANCE", "instances", "Delete ". $_POST['instances_id'], $AUTH->data['users_userid'],null, $_POST['instances_id']);
    finish(true);
} else finish(false);

/**
 *  @OA\Post(
 *      path="/instances/delete.php",
 *      summary="Permanently Delete Instance",
 *      description="Permanently Delete a soft-deleted Instance
 Requires Server permission INSTANCES:PERMANENTLY_DELETE",
 *      operationId="DeleteInstance",
 *      tags={"instances"},
 *      @OA\Response(
 *          response="200",
 *          description="Success",
 *          @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *      ),
 *      @OA\Parameter(
 *          name="instances_id",
 *          in="query",
 *          description="Id of soft-deleted instance to delete",
 *          required="true",
 *          @OA\Schema(
 *              type="number",
 *          ),
 *      ),
 *  )
 */