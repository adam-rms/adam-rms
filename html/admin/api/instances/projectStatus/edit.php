<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_STATUSES:EDIT")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['name'] == "projectsStatuses_assetsReleased") {
        $array[$item['name']] = $item['value'] == "on" ? 1 : 0;
    } else {
        $array[$item['name']] = $item['value'];
    }
}
if (strlen($array['projectsStatuses_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projectsStatuses_deleted", 0);
$DBLIB->where("projectsStatuses_id", $array['projectsStatuses_id']);
if ($array["projectsStatuses_assetsReleased"] == 0) $DBLIB->where("projectsStatuses_assetsReleased", 0); // Once assets are released they cannot be unreleased
$status = $DBLIB->update("projectsStatuses", $array);
if (!$status) finish(false);

$bCMS->auditLog("EDIT", "projectsStatuses", json_encode($array), $AUTH->data['users_userid']);
finish(true);

/**
 *  @OA\Post(
 *      path="/instances/projectStatus/edit.php",
 *      summary="Edit Project Status",
 *      description="Edit Project Status details",
 *      operationId="editProjectStatus",
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
 *          name="formData",
 *          in="query",
 *          description="Project Status Data",
 *          required="true",
 *          @OA\Schema(
 *              type="object",
 *          ),
 *      ),
 *  )
 */