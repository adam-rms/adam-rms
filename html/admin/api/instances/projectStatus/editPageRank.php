<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_STATUSES:EDIT")) die("404");

foreach ($_POST['order'] as $count=>$item) {
    if ($item == "") continue;
    $DBLIB->where("projectsStatuses_id",$item);
    $DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
    if (!$DBLIB->update("projectsStatuses", ["projectsStatuses_rank" => $count], 1)) finish(false);
}
$bCMS->auditLog("RANK-PROJECTSTATUSES", "projectsStatuses", "Set the order of project statuses", $AUTH->data['users_userid']);
finish(true);

/**
 *  @OA\Post(
 *      path="/instances/projectStatus/editPageRank.php",
 *      summary="Edit Project Status Order",
 *      description="Edit status flow order",
 *      operationId="editProjectStatusOrder",
 *      tags={"projectStatus"},
 *      @OA\Response(
 *          response="200",
 *          description="Success",
 *          @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *      ),
 *      @OA\Parameter(
 *          name="order",
 *          in="query",
 *          description="Array of Project Statuses",
 *          required="true",
 *          @OA\Schema(
 *              type="array",
 *          ),
 *      ),
 *  )
 */