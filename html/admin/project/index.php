<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck(20) or !isset($_GET['id'])) die("Sorry - you can't access this page");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_GET['id']);
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->join("users", "projects.projects_manager=users.users_userid", "LEFT");
$PAGEDATA['project'] = $DBLIB->getone("projects", ["projects.*", "clients.clients_name", "users.users_name1", "users.users_name2", "users.users_email"]);
if (!$PAGEDATA['project']) die("404");

$DBLIB->where("auditLog.auditLog_deleted", 0);
$DBLIB->where("auditLog.projects_id", $PAGEDATA['project']['projects_id']);
$DBLIB->join("users", "auditLog.users_userid=users.users_userid", "LEFT");
$DBLIB->orderBy("auditLog.auditLog_timestamp", "DESC");
$PAGEDATA['project']['auditLog'] = $DBLIB->get("auditLog",null, ["auditLog.*", "users.users_name1", "users.users_name2", "users.users_email"]);

$PAGEDATA['pageConfig'] = ["TITLE" => $PAGEDATA["project"]['projects_name'], "BREADCRUMB" => false];

//Edit Options
if ($AUTH->instancePermissionCheck(22)) {
    $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
    $PAGEDATA['clients'] = $DBLIB->get("clients", null, ["clients_id", "clients_name"]);
}
if ($AUTH->instancePermissionCheck(23)) {
    $DBLIB->orderBy("users.users_name1", "ASC");
    $DBLIB->orderBy("users.users_name2", "ASC");
    $DBLIB->orderBy("users.users_created", "ASC");
    $DBLIB->where("users_deleted", 0);
    $DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
    $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
    $DBLIB->where("instances_id",  $AUTH->data['instance']['instances_id']);
    $DBLIB->where("userInstances.userInstances_deleted",  0);
    $PAGEDATA['potentialManagers'] = $DBLIB->get('users', null, ["users.users_name1", "users.users_name2", "users.users_userid"]);
}

//Assets
$DBLIB->where("projects_id", $PAGEDATA['project']['projects_id']);
$DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
$DBLIB->join("assets","assetsAssignments.assets_id=assets.assets_id", "LEFT");
$DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->join("assetCategories", "assetTypes.assetCategories_id=assetCategories.assetCategories_id", "LEFT");
$DBLIB->orderBy("assetCategories.assetCategories_rank", "ASC");
$DBLIB->orderBy("assetTypes.assetTypes_id", "ASC");
$DBLIB->orderBy("assets.assets_tag", "ASC");
$assets = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.*", "manufacturers.manufacturers_name", "assetTypes.*", "assets.*", "assetCategories.assetCategories_name", "assetCategories.assetCategories_fontAwesome"]);

$PAGEDATA['assetsAssigned'] = [];
$PAGEDATA['mass'] = 0;
$PAGEDATA['prices'] = ["subTotal" => 0.0, "discounts" => 0.0, "total" => 0.0];
//Calculate the default pricing for all assets
$PAGEDATA['priceMaths'] = ["string" => "Calculated based on:", "days" => 0, "weeks" => 0];
$start = strtotime(date("d F Y 00:00:00", strtotime($PAGEDATA['project']['projects_dates_deliver_start'])));
$end = strtotime(date("d F Y 23:59:59", strtotime($PAGEDATA['project']['projects_dates_deliver_end'])));
if (date("N", $start) == 6) {
    $PAGEDATA['priceMaths']['weeks'] += 1;
    $PAGEDATA['priceMaths']['string'] .= "\nBegins on Saturday so first weekend charged as one week";
    $start = $start+(86400*2);
} elseif (date("N", $start) == 7) {
    $PAGEDATA['priceMaths']['weeks'] += 1;
    $PAGEDATA['priceMaths']['string'] .= "\nBegins on Sunday so first weekend charged as one week";
    $start = $start+86400;
}
if (date("N", $end) == 6) {
    $PAGEDATA['priceMaths']['weeks'] += 1;
    $PAGEDATA['priceMaths']['string'] .= "\nEnds on Saturday so last weekend charged as one week";
    $end = $end-86400;
} elseif (date("N", $end) == 7) {
    $PAGEDATA['priceMaths']['weeks'] += 1;
    $PAGEDATA['priceMaths']['string'] .= "\nEnds on Sunday so last weekend charged as one week";
    $end = $end-(86400*2);
}

$remaining = strtotime(date("d F Y 23:59:59", $end)) - strtotime(date("d F Y", $start));
if ($remaining > 0) {
    $remaining = ceil($remaining/86400); //Convert to days
    $weeks = floor($remaining/7); //Number of week periods
    if ($weeks > 0) {
        $PAGEDATA['priceMaths']['weeks'] += $weeks;
        $PAGEDATA['priceMaths']['string'] .= "\nAdd " . $weeks . " week period(s) to reflect a period of more than 7 days";
        $remaining = $remaining - ($weeks*7);
    }
    if ($remaining > 2) {
        $PAGEDATA['priceMaths']['string'] .= "\nAdd a week to discount a period of more than 3 days or more that's under 7";
        $PAGEDATA['priceMaths']['weeks'] += 1;
        $remaining = $remaining -7;
    }
    if ($remaining > 0) {
        $PAGEDATA['priceMaths']['days'] += ceil($remaining);
        $PAGEDATA['priceMaths']['string'] .= "\nAdd " . ceil($remaining) . " day period(s)";
    }
}
//End calculation

foreach ($assets as $asset) {
    $PAGEDATA['mass'] += $asset['assetTypes_mass'];

    if ($asset['assetsAssignments_customPrice'] == null) {
        //The actual pricing calculator
        $asset['price'] = 0;
        $asset['price'] += $PAGEDATA['priceMaths']['days']*$asset['assetTypes_dayRate'];
        $asset['price'] += $PAGEDATA['priceMaths']['weeks']*$asset['assetTypes_weekRate'];
    } else $asset['price'] = $asset['assetsAssignments_customPrice'];
    $asset['price'] = round($asset['price'], 2, PHP_ROUND_HALF_UP);
    $PAGEDATA['prices']['subTotal'] += $asset['price'];

    if ($asset['assetsAssignments_discount'] > 0) {
        $asset['discountPrice'] = round(($asset['price']*(1-($asset['assetsAssignments_discount']/100))), 2, PHP_ROUND_HALF_UP);
    } else $asset['discountPrice'] = $asset['price'];

    $PAGEDATA['prices']['discounts'] += ($asset['price'] - $asset['discountPrice']);
    $PAGEDATA['prices']['total'] += $asset['discountPrice'];

    $PAGEDATA['assetsAssigned'][] = $asset;
}

echo $TWIG->render('project/index.twig', $PAGEDATA);
?>
