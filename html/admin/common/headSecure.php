<?php
require_once __DIR__ . '/head.php';
require_once __DIR__ . '/../../common/libs/Auth/main.php';

if (!$GLOBALS['AUTH']->login) {
    $_SESSION['return'] = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: " . $CONFIG['ROOTURL'] . "/login/");
    die('<meta http-equiv="refresh" content="0; url="' . $CONFIG['ROOTURL'] . "/login/" . '" />');
}

$DBLIB->where("((SELECT COUNT(*) FROM assets WHERE assets.assetTypes_id=assetTypes.assetTypes_id AND assets_deleted = '0' AND assets.instances_id = '" . $AUTH->data['instance']['instances_id'] . "') > 0)");
$assetCategories = $DBLIB->getvalue("assetTypes", "DISTINCT assetCategories_id", null);
if ($assetCategories) {
    $DBLIB->orderBy("assetCategories_rank", "ASC");
    $DBLIB->where("(assetCategories_id IN (" . implode(",", $assetCategories) . "))");
    $PAGEDATA['assetCategories'] = $DBLIB->get("assetCategories", null, ["assetCategories.assetCategories_name", "assetCategories.assetCategories_id", "assetCategories.assetCategories_fontAwesome"]);
} else $PAGEDATA['assetCategories'] = [];


$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assets_deleted", 0);
$PAGEDATA['assetCount'] = $DBLIB->getValue("assets", "COUNT(*)");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->orderBy("projects.projects_dates_deliver_start", "ASC");
$DBLIB->orderBy("projects.projects_name", "ASC");
$DBLIB->orderBy("projects.projects_created", "ASC");
$PAGEDATA['projects'] = $DBLIB->get("projects", null, ["projects_id", "projects_archived", "projects_name", "clients_name", "projects_dates_deliver_start", "projects_dates_deliver_end", "projects_status", "projects_manager"]);
if ($AUTH->data['users_selectedProjectID'] != null) {
    foreach ($PAGEDATA['projects'] as $project) {
        if ($project['projects_id'] == $AUTH->data['users_selectedProjectID'] ) {
            $PAGEDATA['thisProject'] = $project;
            break;
        }
    }
} else $PAGEDATA['thisProject'] = false;

$PAGEDATA['USERDATA'] = $GLOBALS['AUTH']->data;
$PAGEDATA['USERDATA']['users_email_md5'] = md5($PAGEDATA['USERDATA']['users_email']);


$GLOBALS['STATUSES'] = [
    0 => [
        "name" => "Added to RMS",
        "description" => "Default",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#F5F5F5",
        "order" => 0,
        "assetsAvailable" => false,
    ],
    1 => [
        "name" => "Targeted",
        "description" => "Being targeted as a lead",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#F5F5F5",
        "order" => 1,
        "assetsAvailable" => false,
    ],
    2 => [
        "name" => "Quote Sent",
        "description" => "Waiting for client confirmation",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#ffdd99",
        "order" => 2,
        "assetsAvailable" => false,
    ],
    3 => [
        "name" => "Confirmed",
        "description" => "Booked in with client",
        "foregroundColour" => "#ffffff",
        "backgroundColour" => "#66ff66",
        "order" => 3,
        "assetsAvailable" => false,
    ],
    4 => [
        "name" => "Prep",
        "description" => "Being prepared for dispatch" ,
        "foregroundColour" => "#000000",
        "backgroundColour" => "#ffdd99",
        "order" => 4,
        "assetsAvailable" => false,
    ],
    5 => [
        "name" => "Dispatched",
        "description" => "Sent to client" ,
        "foregroundColour" => "#ffffff",
        "backgroundColour" => "#66ff66",
        "order" => 5,
        "assetsAvailable" => false,
    ],
    6 => [
        "name" => "Returned",
        "description" => "Waiting to be checked in ",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#ffdd99",
        "order" => 6,
        "assetsAvailable" => false,
    ],
    7 => [
        "name" => "Closed",
        "description" => "Pending move to Archive",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#F5F5F5",
        "order" => 7,
        "assetsAvailable" => false,
    ],
    8 => [
        "name" => "Cancelled",
        "description" => "Event Cancelled",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#F5F5F5",
        "order" => 8,
        "assetsAvailable" => true,
    ],
    9 => [
        "name" => "Lead Lost",
        "description" => "Event Cancelled",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#F5F5F5",
        "order" => 9,
        "assetsAvailable" => true,
    ]
];
$GLOBALS['STATUSES-AVAILABLE'] = [];
foreach ($GLOBALS['STATUSES'] as $key => $status) {
    if ($status['assetsAvailable']) array_push($GLOBALS['STATUSES-AVAILABLE'], $key);
}
usort($GLOBALS['STATUSES'], function($a, $b) {
    return $a['order'] - $b['order'];
});
$PAGEDATA['STATUSES'] = $GLOBALS['STATUSES'];
$PAGEDATA['STATUSESAVAILABLE'] = $GLOBALS['STATUSES-AVAILABLE'];

$USERDATA = $PAGEDATA['USERDATA'];
?>
