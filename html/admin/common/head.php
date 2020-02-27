<?php
require_once __DIR__ . '/../../common/coreHead.php';

$CONFIG['ROOTURL'] = getenv('bCMS__BACKENDURL');


$PAGEDATA = array('CONFIG' => $CONFIG, 'BODY' => true);
//TWIG
//Twig_Autoloader::register();
$TWIGLOADER = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../');
$TWIG = new \Twig\Environment($TWIGLOADER, array(
    'debug' => true,
    'auto_reload' => true
));
$TWIG->addExtension(new \Twig\Extension\DebugExtension());
$TWIG->addFilter(new \Twig\TwigFilter('timeago', function ($datetime) {
    $time = time() - strtotime($datetime);
    $units = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );
    foreach ($units as $unit => $val) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return ($val == 'second')? 'a few seconds ago' :
            (($numberOfUnits>1) ? $numberOfUnits : 'a')
            .' '.$val.(($numberOfUnits>1) ? 's' : '').' ago';
    }
}));
$TWIG->addFilter(new \Twig\TwigFilter('formatsize', function ($var) {
    global $bCMS;
    return $bCMS->formatSize($var);
}));
$TWIG->addFilter(new \Twig\TwigFilter('unclean', function ($var) {
    global $bCMS;
    return $bCMS->unCleanString($var);
}));
$TWIG->addFilter(new \Twig\TwigFilter('permissions', function ($permissionid) {
    global $AUTH;
    if (!$AUTH->login) return false;
    else return $AUTH->permissionCheck($permissionid);
}));
$TWIG->addFilter(new \Twig\TwigFilter('instancePermissions', function ($permissionid) {
    global $AUTH;
    if (!$AUTH->login) return false;
    else return $AUTH->instancePermissionCheck($permissionid);
}));
$TWIG->addFilter(new \Twig\TwigFilter('modifyGet', function ($array) {
    global $bCMS;
    return http_build_query(($bCMS->modifyGet($array)));
}));
$TWIG->addFilter(new \Twig\TwigFilter('randomString', function ($characters) {
    global $bCMS;
    return $bCMS->randomString($characters);
}));
$TWIG->addFilter(new \Twig\TwigFilter('s3URL', function ($fileid, $size = false) {
    global $bCMS;
    return $bCMS->s3URL($fileid, $size);
}));
$TWIG->addFilter(new \Twig\TwigFilter('aTag', function ($id) {
    if ($id == null) return null;
    if ($id <= 9999) return "A-" . sprintf('%04d', $id);
    else return "A-" . $id;
}));

function generateNewTag() {
    global $DBLIB;
    //Get highest current tag
    $DBLIB->orderBy("assets_tag", "DESC");
    $tag = $DBLIB->getone("assets", ["assets_tag"]);
    if ($tag) return intval($tag["assets_tag"])+1;
    else return 1;
}

$GLOBALS['AUTH'] = new bID;





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


$GLOBALS['ASSETASSIGNMENTSTATUSES'] = [
    0 => [
        "name" => "None applicable"
        ],
    1 => ["name" => "Pending pick"],
    2 => ["name" => "Picked"],
    3 => ["name" => "Prepping"],
    4 => ["name" => "Tested for prep"],
    5 => ["name" => "Packed"],
    6 => ["name" => "Dispatched"],
    7 => ["name" => "Awaiting Check-in"],
    8 => ["name" => "Case opened"],
    9 => ["name" => "Unpacked"],
    10 => ["name" => "Tested from return"],
    11 => ["name" => "Stored"]
];

//Project Finance Calculator
function projectFinancials($projectid)
{
    global $DBLIB;
    $return = [];

    $DBLIB->where("projects_id", $projectid);
    $project = $DBLIB->getone("projects", ['instances_id', 'projects_id', 'projects_dates_deliver_start','projects_dates_deliver_end']);
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
    $DBLIB->join("instances", "assets.instances_id=instances.instances_id", "LEFT");
    $DBLIB->orderBy("instances.instances_name", "ASC");
    $DBLIB->orderBy("assetCategories.assetCategories_rank", "ASC");
    $DBLIB->orderBy("assetTypes.assetTypes_id", "ASC");
    $DBLIB->orderBy("assets.assets_tag", "ASC");
    $assets = $DBLIB->get("assetsAssignments", null, ["assetCategories.assetCategories_rank", "assetsAssignments.*", "manufacturers.manufacturers_name", "assetTypes.*", "assets.*", "assetCategories.assetCategories_name", "assetCategories.assetCategories_fontAwesome", "instances.instances_name AS assetInstanceName", "instances.instances_id"]);

    $return['assetsAssigned'] = [];
    $return['assetsAssignedSUB'] = [];
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
    if (($end-$start) > 259200) { //If it's just one weekend it doesn't count as two weeks
        if (date("N", $end) == 6) {
            $return['priceMaths']['weeks'] += 1;
            $return['priceMaths']['string'] .= "\nEnds on Saturday so last weekend charged as one week";
            $end = $end - 86400;
        } elseif (date("N", $end) == 7) {
            $return['priceMaths']['weeks'] += 1;
            $return['priceMaths']['string'] .= "\nEnds on Sunday so last weekend charged as one week";
            $end = $end - (86400 * 2);
        }
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

        $asset['assetTypes_definableFields_ARRAY'] = array_filter(explode(",", $asset['assetTypes_definableFields']));

        if ($asset['instances_id'] != $project['instances_id']) $return['assetsAssignedSUB'][] = $asset;
        else $return['assetsAssigned'][] = $asset;
    }


    $return['payments']['subTotal'] = $return['prices']['total'] + $return['payments']['sales']['total'] + $return['payments']['subHire']['total'] + $return['payments']['staff']['total'];
    $return['payments']['total'] = $return['payments']['subTotal'] - $return['payments']['received']['total'];
    return $return;
}
