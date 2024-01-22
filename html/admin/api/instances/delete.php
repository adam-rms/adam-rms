<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->serverPermissionCheck("INSTANCES:DELETE") or !isset($_POST['instances_id'])) die("404");

$DBLIB->where('instances_id', $_POST['instances_id']);
$DBLIB->where("instances_deleted", 0);
if($DBLIB->update('instances', ["instances_deleted" => 1], 1)) {
    $bCMS->auditLog("SOFT-DELETE-INSTANCE", "instances", "Soft delete ". $_POST['instances_id'], $AUTH->data['users_userid'],null, $_POST['instances_id']);
    finish(true);
} else finish(false);

/**
 *  @OA\Post(
 *      path="/instances/delete.php",
 *      summary="Soft Delete Instance",
 *      description="Soft Delete an Instance
 Requires Server permission INSTANCES:DELETE",
 *      operationId="softDeleteInstance",
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
 *          description="Id of instance to delete",
 *          required="true",
 *          @OA\Schema(
 *              type="number",
 *          ),
 *      ),
 *  )
 */
