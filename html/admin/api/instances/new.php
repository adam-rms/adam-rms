<?php
require_once __DIR__ . '/../apiHeadSecure.php';
require_once __DIR__ . '/../../common/libs/Auth/instanceActions.php';
if (($CONFIG["NEW_INSTANCE_ENABLED"] !== "Enabled" and !$AUTH->serverPermissionCheck("INSTANCES:CREATE")) or !isset($_POST['instances_name'])) die("404");

$instance = $DBLIB->insert("instances", [
    "instances_name" => $_POST['instances_name'],
    "instances_address" => $_POST['instances_name'],
    "instances_website" => $_POST['instances_website'],
    "instances_email" => $_POST['instances_email'],
    "instances_phone" => $_POST['instances_phone'],
    "instances_config_currency" => $_POST['instances_config_currency'],
    "instances_billingUser" => $PAGEDATA['USERDATA']['users_userid'],
    "instances_storageLimit" => 0,
    "instances_storageEnabled" => 1,
    "instances_assetLimit" => 0,
    "instances_userLimit" => 0,
    "instances_planName" => "",
    "instances_suspended" => ($CONFIGCLASS->get('NEW_INSTANCE_SUSPENDED') == "Suspended") ? 1 : 0,
    "instances_suspendedReasonType" => $CONFIGCLASS->get('NEW_INSTANCE_SUSPENDED_REASON_TYPE'),
    "instances_suspendedReason" => $CONFIGCLASS->get('NEW_INSTANCE_SUSPENDED_REASON')
]);
if (!$instance) finish(false, ["code" => "CREATE-INSTANCE-FAIL", "message" => "Could not create new business"]);

$position = $DBLIB->insert("instancePositions", [
    "instances_id" => $instance,
    "instancePositions_displayName" => "Administrator",
    "instancePositions_rank" => 1,
    "instancePositions_actions" => implode(",", array_keys($instanceActions))
]);
if (!$position) finish(false, ["code" => "CREATE-POSITION-FAIL", "message" => "Could not create new business"]);

$userPosition = $DBLIB->insert("userInstances", [
    "users_userid" => $PAGEDATA['USERDATA']['users_userid'],
    "instancePositions_id" => $position,
    "userInstances_label" => $_POST['role'],
]);
if (!$userPosition) finish(false, ["code" => "ADD-USER-TO-INSTANCE-FAIL", "message" => "Could not create new business"]);

$projectType = $DBLIB->insert("projectsTypes", [
    "instances_id" => $instance,
    "projectsTypes_name" => "Full Project"
]);
if (!$projectType) finish(false, ["code" => "ADD-PROJECT-TYPE-FAIL", "message" => "Could not create new project type"]);

$defaultProjectStatuses = [
    [
        "projectsStatuses_name" => "Added to RMS",
        "projectsStatuses_description" => "Default",
        "projectsStatuses_foregroundColour" => "#000000",
        "projectsStatuses_backgroundColour" => "#F5F5F5",
        "projectsStatuses_rank" => 0,
        "projectsStatuses_assetsReleased" => false,
    ],
    [
        "projectsStatuses_name" => "Targeted",
        "projectsStatuses_description" => "Being targeted as a lead",
        "projectsStatuses_foregroundColour" => "#000000",
        "projectsStatuses_backgroundColour" => "#F5F5F5",
        "projectsStatuses_rank" => 1,
        "projectsStatuses_assetsReleased" => false
    ],
    [
        "projectsStatuses_name" => "Quote Sent",
        "projectsStatuses_description" => "Waiting for client confirmation",
        "projectsStatuses_foregroundColour" => "#000000",
        "projectsStatuses_backgroundColour" => "#ffdd99",
        "projectsStatuses_rank" => 2,
        "projectsStatuses_assetsReleased" => false
    ],
    [
        "projectsStatuses_name" => "Confirmed",
        "projectsStatuses_description" => "Booked in with client",
        "projectsStatuses_foregroundColour" => "#ffffff",
        "projectsStatuses_backgroundColour" => "#66ff66",
        "projectsStatuses_rank" => 3,
        "projectsStatuses_assetsReleased" => false
    ],
    [
        "projectsStatuses_name" => "Prep",
        "projectsStatuses_description" => "Being prepared for dispatch",
        "projectsStatuses_foregroundColour" => "#000000",
        "projectsStatuses_backgroundColour" => "#ffdd99",
        "projectsStatuses_rank" => 4,
        "projectsStatuses_assetsReleased" => false
    ],
    [
        "projectsStatuses_name" => "Dispatched",
        "projectsStatuses_description" => "Sent to client",
        "projectsStatuses_foregroundColour" => "#ffffff",
        "projectsStatuses_backgroundColour" => "#66ff66",
        "projectsStatuses_rank" => 5,
        "projectsStatuses_assetsReleased" => false
    ],
    [
        "projectsStatuses_name" => "Returned",
        "projectsStatuses_description" => "Waiting to be checked in ",
        "projectsStatuses_foregroundColour" => "#000000",
        "projectsStatuses_backgroundColour" => "#ffdd99",
        "projectsStatuses_rank" => 6,
        "projectsStatuses_assetsReleased" => false
    ],
    [
        "projectsStatuses_name" => "Closed",
        "projectsStatuses_description" => "Pending move to Archive",
        "projectsStatuses_foregroundColour" => "#000000",
        "projectsStatuses_backgroundColour" => "#F5F5F5",
        "projectsStatuses_rank" => 7,
        "projectsStatuses_assetsReleased" => false
    ],
    [
        "projectsStatuses_name" => "Cancelled",
        "projectsStatuses_description" => "Project Cancelled",
        "projectsStatuses_foregroundColour" => "#000000",
        "projectsStatuses_backgroundColour" => "#F5F5F5",
        "projectsStatuses_rank" => 8,
        "projectsStatuses_assetsReleased" => true
    ],
    [
        "projectsStatuses_name" => "Lead Lost",
        "projectsStatuses_description" => "Project Cancelled",
        "projectsStatuses_foregroundColour" => "#000000",
        "projectsStatuses_backgroundColour" => "#F5F5F5",
        "projectsStatuses_rank" => 9,
        "projectsStatuses_assetsReleased" => true
    ]
];
foreach ($defaultProjectStatuses as $projectStatus) {
    $projectStatus['instances_id'] = $instance;
    $insertProjectStatus = $DBLIB->insert("projectsStatuses", $projectStatus);
    if (!$insertProjectStatus) finish(false, ["code" => "ADD-PROJECT-STATUS-FAIL", "message" => "Could not create new project statuses"]);
}

$count = 0;
foreach (["Pending pick", "Picked", "Prepping", "Tested", "Packed", "Dispatched", "Awaiting Check-in", "Case opened", "Unpacked", "Tested", "Stored"] as $item) {
    $assignmentsStatus = $DBLIB->insert("assetsAssignmentsStatus", [
        "instances_id" => $instance,
        "assetsAssignmentsStatus_name" => $item,
        "assetsAssignmentsStatus_order" => $count
    ]);
    $count += 1;
    if (!$assignmentsStatus) finish(false, ["code" => "ADD-STATUS-FAIL", "message" => "Could not create new assignment status"]);
}



finish(true, null, ["instanceid" => $instance]);

/** @OA\Post(
 *     path="/instances/new.php", 
 *     summary="Create Instance", 
 *     description="Create a new instance  
Requires server permission INSTANCES:CREATE or NEW_INSTANCE_ENABLED to be set to enabled in the server config
", 
 *     operationId="createInstance", 
 *     tags={"instances"}, 
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
 *                 @OA\Property(
 *                     property="instanceid", 
 *                     type="number", 
 *                     description="The instance id",
 *                 ),
 *         ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="404", 
 *         description="Auth Fail",
 *     ), 
 *     @OA\Response(
 *         response="default", 
 *         description="Error",
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
 *     @OA\Parameter(
 *         name="instances_name",
 *         in="query",
 *         description="The instance name",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="instances_website",
 *         in="query",
 *         description="The instance website",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="instances_email",
 *         in="query",
 *         description="The instance email",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="instances_phone",
 *         in="query",
 *         description="The instance phone number",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="role",
 *         in="query",
 *         description="The user's role id",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */
