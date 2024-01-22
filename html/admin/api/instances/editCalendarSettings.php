<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:BUSINESS_SETTINGS:EDIT")) die("Sorry - you can't access this page");
$array = [];
if (!isset($_POST['formData'])) die("404");
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    elseif ($item['value'] == "on") $item['value'] = true;

    $array[$item['name']] = $item['value'];
}
$oldData = json_decode($AUTH->data['instance']['instances_calendarConfig'],true);
$DBLIB->where("instances_id",$AUTH->data['instance']["instances_id"]);
$result = $DBLIB->update("instances", ["instances_calendarConfig" => json_encode($array)]);
if (!$result) finish(false, ["code" => "UPDATE-FAIL", "message"=> "Could not update instance calendar config"]);
else {
    $bCMS->auditLog("EDIT-INSTANCE", "instances", "Calendar - " . json_encode($array), $AUTH->data['users_userid'],null, $AUTH->data['instance']["instances_id"]);
    finish(true);
}

/**
 *  @OA\Post(
 *      path="/instances/editCalendarSettings.php",
 *      summary="Edit Calendar Settings",
 *      description="Edit Settings related to calendars, including formatting and filters",
 *      operationId="editCalendarSettings",
 *      tags={"instanceCalendarSettings"},
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
 *          description="Calendar Options",
 *          required="true",
 *          @OA\Schema(
 *              type="object",
 *          ),
 *      ),
 *  )
 */