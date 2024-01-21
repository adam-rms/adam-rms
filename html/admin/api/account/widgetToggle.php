<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['widgetName'])) finish(false, ["code" => null, "message"=> "Attribute error"]);
$widgetName = $bCMS->sanitizeString($_POST['widgetName']);
$currentWidgets = explode(",",$AUTH->data['users_widgets']);
$key = array_search($widgetName, $currentWidgets);
if ($key !== false) {
    unset($currentWidgets[$key]);
} else {
    array_push($currentWidgets, $widgetName);
}
$DBLIB->where ('users_userid', $AUTH->data['users_userid']);
if ($DBLIB->update ('users', ["users_widgets" => implode(",", $currentWidgets)])) finish(true);
else finish(false, ["code" => null, "message"=> "Update error"]);

/**
 *  @OA\Post(
 *      path="/account/widgetToggle.php",
 *      summary="Toggle Widget",
 *      description="Toggle the visibility of a widget on the dashboard",
 *      operationId="widgetToggle",
 *      tags={"account"},
 *      @OA\Response(
 *          response="200",
 *          description="Success",
 *          @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *      ),
 *      @OA\Parameter(
 *          name="widgetName",
 *          in="query",
 *          description="Name of the Widget to toggle",
 *          required="true",
 *          @OA\Schema(
 *              type="string",
 *          ),
 *      ),
 *  )
 */
