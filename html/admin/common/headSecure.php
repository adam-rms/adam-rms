<?php
require_once __DIR__ . '/head.php';
require_once __DIR__ . '/../assets/widgets/statsWidgets.php'; //Stats on homepage etc.

if (!$GLOBALS['AUTH']->login) {
    if ($CONFIG['DEV']) die("<h2>Debugging enabled - auth fail debug details</h2>" . $GLOBALS['AUTH']->debug . "<br/><br/><br/>Login false - redirect to <a href='" . $CONFIG['ROOTURL'] . "/login/'>" . $CONFIG['ROOTURL'] . "/login/</a>");
    $_SESSION['return'] = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: " . $CONFIG['ROOTURL'] . "/login/");
    die('<meta http-equiv="refresh" content="0; url="' . $CONFIG['ROOTURL'] . "/login/" . '" />');
}

$DBLIB->where("((SELECT COUNT(*) FROM assets WHERE assets.assetTypes_id=assetTypes.assetTypes_id AND assets_deleted = '0' AND assets.instances_id = '" . $AUTH->data['instance']['instances_id'] . "') > 0)");
$assetCategories = $DBLIB->getvalue("assetTypes", "DISTINCT assetCategories_id", null);
if ($assetCategories) {
    $DBLIB->orderBy("assetCategoriesGroups.assetCategoriesGroups_order", "ASC");
    $DBLIB->orderBy("assetCategories.assetCategories_rank", "ASC");
    $DBLIB->where("(assetCategories_id IN (" . implode(",", $assetCategories) . "))");
    $DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
    $PAGEDATA['assetCategories'] = $DBLIB->get("assetCategories", null, ["assetCategories.assetCategories_name", "assetCategories.assetCategories_id", "assetCategories.assetCategories_fontAwesome", "assetCategoriesGroups_name", "assetCategoriesGroups_fontAwesome"]);
} else $PAGEDATA['assetCategories'] = [];

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_archived", 0);
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->orderBy("projects.projects_dates_deliver_start", "ASC");
$DBLIB->orderBy("projects.projects_name", "ASC");
$DBLIB->orderBy("projects.projects_created", "ASC");
$PAGEDATA['projects'] = $DBLIB->get("projects", null, ["projects_id", "projects_archived", "projects_name", "clients_name", "projects_dates_deliver_start", "projects_dates_deliver_end","projects_dates_use_start", "projects_dates_use_end", "projects_status", "projects_manager"]);
$PAGEDATA['thisProject'] = false;
if ($AUTH->data['users_selectedProjectID'] != null) {
    foreach ($PAGEDATA['projects'] as $project) {
        if ($project['projects_id'] == $AUTH->data['users_selectedProjectID'] ) {
            $PAGEDATA['thisProject'] = $project;
            break;
        }
    }
    if (!$PAGEDATA['thisProject']) {
        //They're browsing with a project selected from another instance so we need to go grab it
        $DBLIB->where("projects.projects_id", $AUTH->data['users_selectedProjectID']);
        $DBLIB->where("projects.instances_id IN (" . implode(",", $AUTH->data['instance_ids']) . ")"); //Duplicated elsewhere
        $DBLIB->where("projects.projects_deleted", 0);
        $DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
        $DBLIB->join("instances", "projects.instances_id=instances.instances_id", "LEFT");
        $DBLIB->orderBy("projects.projects_dates_deliver_start", "ASC");
        $DBLIB->orderBy("projects.projects_name", "ASC");
        $DBLIB->orderBy("projects.projects_created", "ASC");
        $PAGEDATA['thisProject'] = $DBLIB->getone("projects", null, ["projects_id", "projects_archived", "projects_name", "clients_name", "projects_dates_deliver_start", "projects_dates_deliver_end","projects_dates_use_start", "projects_dates_use_end", "projects_status", "projects_manager", "instances.instances_name"]);
    }
}

if ($AUTH->data['instance']['instances_weekStartDates'] != null) {
    $dates = explode("\n", $AUTH->data['instance']['instances_weekStartDates']);
    $AUTH->data['instance']['weekStartDates'] = [];
    foreach ($dates as $date) {
        array_push($AUTH->data['instance']['weekStartDates'], strtotime($date)*1000);
    }
    unset($dates);
    sort($AUTH->data['instance']['weekStartDates']);
} else $AUTH->data['instance']['weekStartDates'] = false;


$PAGEDATA['USERDATA'] = $GLOBALS['AUTH']->data;
$PAGEDATA['USERDATA']['users_email_md5'] = md5($PAGEDATA['USERDATA']['users_email']);


if ($CONFIG['DEV'] and !$AUTH->permissionCheck(17) and !$PAGEDATA['USERDATA']['viewSiteAs']) {
    die("Sorry - you can't use this development version of the site - please visit adam-rms.com");
}

$USERDATA = $PAGEDATA['USERDATA']; //TODO - remove as depreciated
?>
