<?php
require_once __DIR__ . '/../../common/coreHead.php';
require_once __DIR__ . '/../../common/libs/Auth/main.php';

$CONFIG['ROOTURL'] = $_ENV['bCMS__BACKENDURL'];

function compareFloats($a,$b) {
    if ($b == 0) return ($a === $b);
    if (abs(($a-$b)/$b) < 0.00001) return true;
    else return false;
}

$PAGEDATA = array('CONFIG' => $CONFIG, 'BODY' => true);
//TWIG
//Twig_Autoloader::register();
$TWIGLOADER = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../');
if ($CONFIG['DEV']) {
    $TWIG = new \Twig\Environment($TWIGLOADER, array(
        'debug' => true,
        'auto_reload' => true,
        'charset' => 'utf-8'
    ));
    $TWIG->addExtension(new \Twig\Extension\DebugExtension());
} else {
    $TWIG = new \Twig\Environment($TWIGLOADER, array(
        'debug' => false,
        'auto_reload' => false,
        'cache' => __DIR__ . '/twigCache/',
        'charset' => 'utf-8'
    ));
}

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
$TWIG->addFilter(new \Twig\TwigFilter('fontAwesomeFile', function ($extension) {
    switch (strtolower($extension)) {
        case "gif":
            return 'fa-file-image';
            break;

        case "jpeg":
            return 'fa-file-image';
            break;

        case "jpg":
            return 'fa-file-image';
            break;

        case "png":
            return 'fa-file-image';
            break;

        case "pdf":
            return 'fa-file-pdf';
            break;

        case "doc":
            return 'fa-file-word';
            break;

        case "docx":
            return 'fa-file-word';
            break;

        case "ppt":
            return 'fa-file-powerpoint';
            break;

        case "pptx":
            return 'fa-file-powerpoint';
            break;

        case "xls":
            return 'fa-file-excel';
            break;

        case "xlsx":
            return 'fa-file-excel';
            break;

        case "csv":
            return 'fa-file-csv';
            break;

        case "aac":
            return 'fa-file-audio';
            break;

        case "mp3":
            return 'fa-file-audio';
            break;

        case "ogg":
            return 'fa-file-audio';
            break;

        case "avi":
            return 'fa-file-video';
            break;

        case "flv":
            return 'fa-file-video';
            break;

        case "mkv":
            return 'fa-file-video';
            break;

        case "mp4":
            return 'fa-file-video';
            break;

        case "gz":
            return 'fa-file-archive';
            break;

        case "zip":
            return 'fa-file-archive';
            break;

        case "css":
            return 'fa-file-code';
            break;

        case "html":
            return 'fa-file-code';
            break;

        case "js":
            return 'fa-file-code';
            break;

        case "txt":
            return 'fa-file-alt';
            break;
        default:
            return 'fa-file';
            break;

    }
}));
$TWIG->addFilter(new \Twig\TwigFilter('aTag', function ($id) {
    if ($id == null) return null;
    if ($id <= 9999) return "A-" . sprintf('%04d', $id);
    else return "A-" . $id;
}));
$TWIG->addFilter(new \Twig\TwigFilter('md5', function ($id) {
    return md5($id);
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

function assetFlagsAndBlocks($assetid) {
    global $DBLIB;
    $DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
    $DBLIB->where("(maintenanceJobs.maintenanceJobs_blockAssets = 1 OR maintenanceJobs.maintenanceJobs_flagAssets = 1)");
    $DBLIB->where("(FIND_IN_SET(" .$assetid . ", maintenanceJobs.maintenanceJobs_assets) > 0)");
    $DBLIB->join("maintenanceJobsStatuses", "maintenanceJobs.maintenanceJobsStatuses_id=maintenanceJobsStatuses.maintenanceJobsStatuses_id", "LEFT");
    //$DBLIB->join("users AS userCreator", "userCreator.users_userid=maintenanceJobs.maintenanceJobs_user_creator", "LEFT");
    //$DBLIB->join("users AS userAssigned", "userAssigned.users_userid=maintenanceJobs.maintenanceJobs_user_assignedTo", "LEFT");
    $DBLIB->orderBy("maintenanceJobs.maintenanceJobs_priority", "DESC");
    $jobs = $DBLIB->get('maintenanceJobs', null, ["maintenanceJobs.maintenanceJobs_id", "maintenanceJobs.maintenanceJobs_faultDescription", "maintenanceJobs.maintenanceJobs_title", "maintenanceJobs.maintenanceJobs_faultDescription", "maintenanceJobs.maintenanceJobs_flagAssets", "maintenanceJobs.maintenanceJobs_blockAssets","maintenanceJobsStatuses.maintenanceJobsStatuses_name"]);
    $return = ["BLOCK" => [], "FLAG" => [], "COUNT" => ["BLOCK" => 0, "FLAG" => 0]];
    if (!$jobs) return $return;
    foreach ($jobs as $job) {
        if ($job["maintenanceJobs_blockAssets"] == 1) {
            $return['BLOCK'][] = $job;
            $return['COUNT']['BLOCK'] += 1;
        }
        if ($job["maintenanceJobs_flagAssets"] == 1) {
            $return['FLAG'][] = $job;
            $return['COUNT']['FLAG'] += 1;
        }
    }
    return $return;
}
class projectFinance {
    public function durationMaths($projects_dates_deliver_start,$projects_dates_deliver_end) {
        //Calculate the default pricing for all assets
        $return = ["string" => "Calculated based on:", "days" => 0, "weeks" => 0];
        $start = strtotime(date("d F Y 00:00:00", strtotime($projects_dates_deliver_start)));
        $end = strtotime(date("d F Y 23:59:59", strtotime($projects_dates_deliver_end)));
        if (date("N", $start) == 6) {
            $return['weeks'] += 1;
            $return['string'] .= "\nBegins on Saturday so first weekend charged as one week";
            $start = $start + (86400 * 2);
        } elseif (date("N", $start) == 7) {
            $return['weeks'] += 1;
            $return['string'] .= "\nBegins on Sunday so first weekend charged as one week";
            $start = $start + 86400;
        }
        if (($end-$start) > 259200) { //If it's just one weekend it doesn't count as two weeks
            if (date("N", $end) == 6) {
                $return['weeks'] += 1;
                $return['string'] .= "\nEnds on Saturday so last weekend charged as one week";
                $end = $end - 86400;
            } elseif (date("N", $end) == 7) {
                $return['weeks'] += 1;
                $return['string'] .= "\nEnds on Sunday so last weekend charged as one week";
                $end = $end - (86400 * 2);
            }
        }

        $remaining = strtotime(date("d F Y 23:59:59", $end)) - strtotime(date("d F Y", $start));
        if ($remaining > 0) {
            $remaining = ceil($remaining / 86400); //Convert to days
            $weeks = floor($remaining / 7); //Number of week periods
            if ($weeks > 0) {
                $return['weeks'] += $weeks;
                $return['string'] .= "\nAdd " . $weeks . " week period(s) to reflect a period of more than 7 days";
                $remaining = $remaining - ($weeks * 7);
            }
            if ($remaining > 2) {
                $return['string'] .= "\nAdd a week to discount a period of more than 3 days or more that's under 7";
                $return['weeks'] += 1;
                $remaining = $remaining - 7;
            }
            if ($remaining > 0) {
                $return['days'] += ceil($remaining);
                $return['string'] .= "\nAdd " . ceil($remaining) . " day period(s)";
            }
        }
        return $return;
    }
}

class projectFinanceCacher {
    //This class assumes that the projectid has been validated as within the instance
    private $data,$projectid;
    private $changesMade = false;
    public function __construct($projectid) {
        //Reset the data
        $this->projectid = $projectid;
        $this->data = [
            "projectsFinanceCache_equipmentSubTotal" =>0.0,
            "projectsFinanceCache_equiptmentDiscounts" =>0.0,
            "projectsFinanceCache_salesTotal" =>0.0,
            "projectsFinanceCache_staffTotal" =>0.0,
            "projectsFinanceCache_externalHiresTotal" =>0.0,
            "projectsFinanceCache_paymentsReceived" =>0.0,
            "projectsFinanceCache_value"=>0.0,
            "projectsFinanceCache_mass"=>0.0
        ];
    }
    public function save() {
        //Process the changes at the end of the script
        global $DBLIB;
        if ($this->changesMade) {
            $dataToUpload = [];
            foreach ($this->data as $key => $value) {
                //Put it into a format for mysql
                if ($value != 0) $dataToUpload[$key] = $DBLIB->inc($value);
            }
            $dataToUpload['projectsFinanceCache_timestampUpdated'] = date("Y-m-d H:i:s");
            $dataToUpload['projectsFinanceCache_equiptmentTotal'] = $DBLIB->inc($this->data["projectsFinanceCache_equipmentSubTotal"] - $this->data['projectsFinanceCache_equiptmentDiscounts']);
            $dataToUpload['projectsFinanceCache_grandTotal'] = $DBLIB->inc((($this->data["projectsFinanceCache_equipmentSubTotal"] - $this->data['projectsFinanceCache_equiptmentDiscounts']) + $this->data['projectsFinanceCache_salesTotal'] + $this->data['projectsFinanceCache_staffTotal'] + $this->data["projectsFinanceCache_externalHiresTotal"]) - $this->data['projectsFinanceCache_paymentsReceived']);
            $DBLIB->where("projects_id", $this->projectid);
            $DBLIB->orderBy("projectsFinanceCache_timestamp", "DESC");
            return $DBLIB->update("projectsFinanceCache", $dataToUpload, 1); //Update the most recent cache datapoint
        } else return true;
    }
    public function adjust($key,$value) {
        if ($value !== 0 or $value !== null) {
            $this->changesMade = true;
            $this->data[$key] += $value;
        }
    }
    public function adjustPayment($paymentType,$value) {
        switch ($paymentType) {
            case 1:
                $key = 'projectsFinanceCache_paymentsReceived';
                break;
            case 2:
                $key = 'projectsFinanceCache_salesTotal';
                break;
            case 3:
                $key = 'projectsFinanceCache_externalHiresTotal';
                break;
            case 4:
                $key = 'projectsFinanceCache_staffTotal';
                break;
            default:
                return false;
        }
        return $this->adjust($key,$value);
    }
}
