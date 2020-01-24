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
$PAGEDATA['projects'] = $DBLIB->get("projects", null, ["projects_id", "projects_archived", "projects_name", "clients_name", "projects_dates_deliver_start", "projects_dates_deliver_end","projects_dates_use_start", "projects_dates_use_end", "projects_status", "projects_manager"]);
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

//Project Finance Calculator
function projectFinancials($projectid)
{
    global $DBLIB;
    $return = [];

    $DBLIB->where("projects_id", $projectid);
    $project = $DBLIB->getone("projects", ['projects_id', 'projects_dates_deliver_start','projects_dates_deliver_end']);
    if (!$project) return false;

    $DBLIB->where("payments.payments_deleted", 0);
    $DBLIB->orderBy("payments.payments_date", "ASC");
    $DBLIB->where("payments.projects_id", $projectid);
    $payments = $DBLIB->get("payments");
    $return['payments'] = ["received" => ["ledger" => [], "total" => 0.0], "sales" => ["ledger" => [], "total" => 0.0], "subHire" => ["ledger" => [], "total" => 0.0], "staff" => ["ledger" => [], "total" => 0.0]];
    foreach ($payments as $payment) {
        switch ($payment['payments_type']) {
            case 1:
                $return['payments']['received']['ledger'][] = $payment;
                $return['payments']['received']['total'] += $payment['payments_amount'];
                break;
            case 2:
                $return['payments']['sales']['ledger'][] = $payment;
                $return['payments']['sales']['total'] += ($payment['payments_amount']*$payment['payments_quantity']);
                break;
            case 3:
                $return['payments']['subHire']['ledger'][] = $payment;
                $return['payments']['subHire']['total'] += ($payment['payments_amount']*$payment['payments_quantity']);
                break;
            case 4:
                $return['payments']['staff']['ledger'][] = $payment;
                $return['payments']['staff']['total'] += ($payment['payments_amount']*$payment['payments_quantity']);
                break;
        }
    }
    $return['payments']['received']['total'] = round($return['payments']['received']['total'], 2, PHP_ROUND_HALF_UP);
    $return['payments']['sales']['total'] = round($return['payments']['sales']['total'], 2, PHP_ROUND_HALF_UP);
    $return['payments']['subHire']['total'] = round($return['payments']['subHire']['total'], 2, PHP_ROUND_HALF_UP);
    $return['payments']['staff']['total'] = round($return['payments']['staff']['total'], 2, PHP_ROUND_HALF_UP);

    //Assets
    $DBLIB->where("projects_id", $projectid);
    $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
    $DBLIB->join("assets", "assetsAssignments.assets_id=assets.assets_id", "LEFT");
    $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
    $DBLIB->join("assetCategories", "assetTypes.assetCategories_id=assetCategories.assetCategories_id", "LEFT");
    $DBLIB->orderBy("assetCategories.assetCategories_rank", "ASC");
    $DBLIB->orderBy("assetTypes.assetTypes_id", "ASC");
    $DBLIB->orderBy("assets.assets_tag", "ASC");
    $assets = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.*", "manufacturers.manufacturers_name", "assetTypes.*", "assets.*", "assetCategories.assetCategories_name", "assetCategories.assetCategories_fontAwesome"]);

    $return['assetsAssigned'] = [];
    $return['mass'] = 0.0;
    $return['value'] = 0.0;
    $return['prices'] = ["subTotal" => 0.0, "discounts" => 0.0, "total" => 0.0];
    //Calculate the default pricing for all assets
    $return['priceMaths'] = ["string" => "Calculated based on:", "days" => 0, "weeks" => 0];
    $start = strtotime(date("d F Y 00:00:00", strtotime($project['projects_dates_deliver_start'])));
    $end = strtotime(date("d F Y 23:59:59", strtotime($project['projects_dates_deliver_end'])));
    if (date("N", $start) == 6) {
        $return['priceMaths']['weeks'] += 1;
        $return['priceMaths']['string'] .= "\nBegins on Saturday so first weekend charged as one week";
        $start = $start + (86400 * 2);
    } elseif (date("N", $start) == 7) {
        $return['priceMaths']['weeks'] += 1;
        $return['priceMaths']['string'] .= "\nBegins on Sunday so first weekend charged as one week";
        $start = $start + 86400;
    }
    if (date("N", $end) == 6) {
        $return['priceMaths']['weeks'] += 1;
        $return['priceMaths']['string'] .= "\nEnds on Saturday so last weekend charged as one week";
        $end = $end - 86400;
    } elseif (date("N", $end) == 7) {
        $return['priceMaths']['weeks'] += 1;
        $return['priceMaths']['string'] .= "\nEnds on Sunday so last weekend charged as one week";
        $end = $end - (86400 * 2);
    }

    $remaining = strtotime(date("d F Y 23:59:59", $end)) - strtotime(date("d F Y", $start));
    if ($remaining > 0) {
        $remaining = ceil($remaining / 86400); //Convert to days
        $weeks = floor($remaining / 7); //Number of week periods
        if ($weeks > 0) {
            $return['priceMaths']['weeks'] += $weeks;
            $return['priceMaths']['string'] .= "\nAdd " . $weeks . " week period(s) to reflect a period of more than 7 days";
            $remaining = $remaining - ($weeks * 7);
        }
        if ($remaining > 2) {
            $return['priceMaths']['string'] .= "\nAdd a week to discount a period of more than 3 days or more that's under 7";
            $return['priceMaths']['weeks'] += 1;
            $remaining = $remaining - 7;
        }
        if ($remaining > 0) {
            $return['priceMaths']['days'] += ceil($remaining);
            $return['priceMaths']['string'] .= "\nAdd " . ceil($remaining) . " day period(s)";
        }
    }
    //End calculation
    $return['assetTypesCounter'] = [];

    foreach ($assets as $asset) {
        $return['mass'] += $asset['assetTypes_mass'];
        $return['value'] += $asset['assetTypes_value'];

        if (isset($return['assetTypesCounter'][$asset['assetTypes_id']])) $return['assetTypesCounter'][$asset['assetTypes_id']] += 1;
        else $return['assetTypesCounter'][$asset['assetTypes_id']] = 1;

        if ($asset['assetsAssignments_customPrice'] == null) {
            //The actual pricing calculator
            $asset['price'] = 0;
            $asset['price'] += $return['priceMaths']['days'] * $asset['assetTypes_dayRate'];
            $asset['price'] += $return['priceMaths']['weeks'] * $asset['assetTypes_weekRate'];
        } else $asset['price'] = $asset['assetsAssignments_customPrice'];
        $asset['price'] = round($asset['price'], 2, PHP_ROUND_HALF_UP);
        $return['prices']['subTotal'] += $asset['price'];

        if ($asset['assetsAssignments_discount'] > 0) {
            $asset['discountPrice'] = round(($asset['price'] * (1 - ($asset['assetsAssignments_discount'] / 100))), 2, PHP_ROUND_HALF_UP);
        } else $asset['discountPrice'] = $asset['price'];

        $return['prices']['discounts'] += ($asset['price'] - $asset['discountPrice']);
        $return['prices']['total'] += $asset['discountPrice'];

        $return['assetsAssigned'][] = $asset;
    }


    $return['payments']['subTotal'] = $return['prices']['total'] + $return['payments']['sales']['total'] + $return['payments']['subHire']['total'] + $return['payments']['staff']['total'];
    $return['payments']['total'] = $return['payments']['subTotal'] - $return['payments']['received']['total'];
    return $return;
}

if ($CONFIG['DEV'] and !$AUTH->permissionCheck(17) and !$PAGEDATA['USERDATA']['viewSiteAs']) {
    die("Sorry - you can't use this development version of the site - please visit adam-rms.xyz");
}

$USERDATA = $PAGEDATA['USERDATA'];
?>
