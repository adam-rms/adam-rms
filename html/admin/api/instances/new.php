<?php
require_once __DIR__ . '/../apiHeadSecure.php';
require_once __DIR__ . '/../../../common/libs/Auth/instanceActions.php';
if (!$AUTH->serverPermissionCheck("INSTANCES:CREATE") or !isset($_POST['instances_name'])) die("404");

$instance = $DBLIB->insert("instances", [
    "instances_name" => $_POST['instances_name'],
    "instances_address" => $_POST['instances_name'],
    "instances_website" => $_POST['instances_website'],
    "instances_email" => $_POST['instances_email'],
    "instances_phone" => $_POST['instances_phone'],
    "instances_plan" => "trial"
]);
if (!$instance) finish(false, ["code" => "CREATE-INSTANCE-FAIL", "message"=> "Could not create new business"]);

$position = $DBLIB->insert("instancePositions", [
    "instances_id" => $instance,
    "instancePositions_displayName" => "Administrator",
    "instancePositions_rank" => 1,
    "instancePositions_actions" => array_keys($instanceActions)
]);
if (!$position) finish(false, ["code" => "CREATE-POSITION-FAIL", "message"=> "Could not create new business"]);

$userPosition = $DBLIB->insert("userInstances", [
    "users_userid" => $PAGEDATA['USERDATA']['users_userid'],
    "instancePositions_id" => $position,
    "userInstances_label" => $_POST['role'],
]);
if (!$userPosition) finish(false, ["code" => "ADD-USER-TO-INSTANCE-FAIL", "message"=> "Could not create new business"]);

$projectType = $DBLIB->insert("projectsTypes", [
    "instances_id" => $instance,
    "projectsTypes_name" => "Full Project"
]);
if (!$projectType) finish(false, ["code" => "ADD-PROJECT-TYPE-FAIL", "message"=> "Could not create new project type"]);


$count = 0;
foreach (["Pending pick","Picked","Prepping","Tested","Packed","Dispatched","Awaiting Check-in","Case opened","Unpacked","Tested","Stored"] as $item) {
    $assignmentsStatus = $DBLIB->insert("assetsAssignmentsStatus", [
        "instances_id" => $instance,
        "assetsAssignmentsStatus_name" => $item,
        "assetsAssignmentsStatus_order" => $count
    ]);
    $count += 1;
    if (!$assignmentsStatus) finish(false, ["code" => "ADD-STATUS-FAIL", "message"=> "Could not create new assignment status"]);
}



finish(true, null, ["instanceid" => $instance]);