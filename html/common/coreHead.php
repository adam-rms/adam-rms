<?php
require_once __DIR__ . '/config.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\CloudFront\CloudFrontClient;
use Aws\Exception\AwsException;

/* DATBASE CONNECTION */
$DBLIB = new MysqliDb ([
                'host' => $CONFIG['DB_HOSTNAME'],
                'username' => $CONFIG['DB_USERNAME'],
                'password' => $CONFIG['DB_PASSWORD'],
                'db'=> $CONFIG['DB_DATABASE'],
                'port' => 3306,
                //'prefix' => 'adamrms_',
                'charset' => 'utf8'
        ]);

/* FUNCTIONS */
class bCMS {
    function sanitizeString($var) {
        global $DBLIB;
        //Setup Sanitize String Function
        //$var = strip_tags($var);
        $var = htmlspecialchars($var,ENT_NOQUOTES);
        //$var = stripslashes($var);
        return $var;
    }
    function sanitizeStringMYSQL($var) {
        global $DBLIB;
        return $DBLIB->escape($this->sanitizeString($var));
    }
    function randomString($length = 10, $stringonly = false) { //Generate a random string
        $characters = 'abcdefghkmnopqrstuvwxyzABCDEFGHKMNOPQRSTUVWXYZ';
        if (!$stringonly) $characters .= '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    function cleanString($var) {
        //HTML Purification
        //$var = str_replace(array("\r", "\n"), '<br>', $var); //Replace newlines

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null);
        $config->set('AutoFormat.Linkify', true);
        $purifier = new HTMLPurifier($config);
        $clean_html = $purifier->purify($var);

        $clean_html = urlencode($clean_html); //Url encoding stops \ problems!

        global $DBLIB;
        return $DBLIB->escape($clean_html);
    }
    function unCleanString($var) {
        return urldecode($var);
    }
    function formatSize($bytes) {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 1) . ' GB';
        } elseif ($bytes >= 100000) {
            $bytes = number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 0) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }
        return $bytes;
    }
    function modifyGet($array) {
        //Used to setup links that don't affect search terms etc.
        foreach ($array as $key=>$value) {
            $_GET[$key] = $value;
        }
        return $_GET;
    }
    function auditLog($actionType = null, $table = null, $revelantData = null, $userid = null, $useridTo = null, $projectid = null, $targetid = null) { //Keep an audit trail of actions - $userid is this user, and $useridTo is who this action was done to if it was at all
        global $DBLIB;
        $data = [
            "auditLog_actionType" => $actionType,
            "auditLog_actionTable" => $table,
            "auditLog_actionData" =>  $revelantData,
            "auditLog_timestamp" =>  date("Y-m-d H:i:s"),
            "projects_id" => $projectid,
            "auditLog_targetID" => ($targetid ? $this->sanitizeString($targetid) : null)
        ];
        if ($userid > 0) $data["users_userid"] = $this->sanitizeString($userid);
        if ($useridTo > 0) $data["auditLog_actionUserid"] = $this->sanitizeString($useridTo);

        if ($DBLIB->insert("auditLog", $data)) return true;
        else throw new Exception("Could not audit log - " . $DBLIB->getLastError());
    }
    function s3List($typeid, $subTypeid = false, $sort = 's3files_meta_uploaded', $sortOrder = 'ASC', $limit = null) {
        global $DBLIB, $CONFIG;
        $DBLIB->where("s3files_meta_type", $typeid);
        if ($subTypeid) $DBLIB->where("s3files_meta_subType", $subTypeid);
        $DBLIB->where("(s3files_meta_deleteOn >= '". date("Y-m-d H:i:s") . "' OR s3files_meta_deleteOn IS NULL)"); //If the file is to be deleted soon or has been deleted don't let them download it
        $DBLIB->where("s3files_meta_physicallyStored",1); //If we've lost the file or deleted it we can't actually let them download it
        $DBLIB->orderBy($sort, $sortOrder);
        return $DBLIB->get("s3files", $limit, ["s3files_id", "s3files_extension", "s3files_name","s3files_meta_size", "s3files_meta_uploaded"]);
    }
    function s3URL($fileid, $size = false, $forceDownload = false, $expire = '+10 minutes', $cloudfront = true) {
        global $DBLIB, $CONFIG,$AUTH;
        /*
         * File interface for Amazon AWS S3.
         *  Parameters
         *      f (required) - the file id as specified in the database
         *      s (filesize) - false to get the original - available is "tiny" (100px) "small" (500px) "medium" (800px) "large" (1500px)
         *      d (optional, default false) - should a download be forced or should it be displayed in the browser? (if set it will download)
         *      e (optional, default 1 minute) - when should the link expire? Must be a string describing how long in words basically. If this file type has security features then it will default to 1 minute.
         */
        $fileid = $this->sanitizeString($fileid);
        if (strlen($fileid) < 1) return false;
        $DBLIB->where("s3files_id", $fileid);
        $DBLIB->where("(s3files_meta_deleteOn >= '". date("Y-m-d H:i:s") . "' OR s3files_meta_deleteOn IS NULL)"); //If the file is to be deleted soon or has been deleted don't let them download it
        $DBLIB->where("s3files_meta_physicallyStored",1); //If we've lost the file or deleted it we can't actually let them download it
        $file = $DBLIB->getone("s3files");
        if (!$file) return false;
        if ($size and false) { //disabled as at the moment the filenames are random so there's no way that this ever works out correct!
            switch ($size) {
                case "tiny":
                    $file['s3files_filename'] .= ' (tiny)';
                    break; //The want the original
                case "small":
                    $file['s3files_filename'] .= ' (small)';
                    break; //The want the original
                case "medium":
                    $file['s3files_filename'] .= ' (medium)';
                    break; //The want the original
                case "large":
                    $file['s3files_filename'] .= ' (large)';
                    break; //The want the original
                default:
                    //They want the original
            }
        }
        if ($expire == null or $expire === false) $expire = '+10 minutes';
        $file['expiry'] = $expire;

        $instanceIgnore = false;
        switch ($file['s3files_meta_type']) {
            case 1:
                $instanceIgnore = true;
                //This is a user thumbnail
                break;
            case 2:
                $instanceIgnore = true;
                // Asset type thumbnail
            case 3:
                // Asset type file
            case 4:
                // Asset file
            case 5:
                $instanceIgnore = true;
                // Instance thumbnail
            case 6:
                // Instance file
            case 7:
                //Project file
            case 8:
                // Maintenance job file
            case 9:
                $instanceIgnore = true;
                // User thumbnail
            case 10:
                $instanceIgnore = true;
                //Instance email thumbnail
            case 11:
                // Location file
            case 12:
                //Module thumbnail
            case 13:
                //Module step image
            default:
                //There are no specific requirements for this file so not to worry.
        }
        if ($instanceIgnore != true and $file["instances_id"] != $AUTH->data['instance']['instances_id']) return false;

        //Generate the url

        if ($cloudfront) {
            // Create a CloudFront Client to sign the string
            $CloudFrontClient = new Aws\CloudFront\CloudFrontClient([
                'profile' => 'default',
                'version' => '2014-11-06',
                'region' => 'us-east-2'
            ]);
            $signedUrlCannedPolicy = $CloudFrontClient->getSignedUrl([
                'url' => $CONFIG['AWS']["CLOUDFRONT"]["URL"] . $file['s3files_path'] . "/" . $file['s3files_filename'] . '.' . $file['s3files_extension'],
                'expires' => strtotime($file['expiry']),
                'private_key' => $CONFIG['AWS']["CLOUDFRONT"]["PRIVATEKEY"],
                'key_pair_id' => $CONFIG['AWS']["CLOUDFRONT"]["KEYPAIRID"]
            ]);
            return $signedUrlCannedPolicy;
        } else {
            //Download direct from S3
            $s3Client = new Aws\S3\S3Client([
                'region' => $file["s3files_region"],
                'endpoint' => "https://" . $file["s3files_endpoint"],
                'version' => 'latest',
                'credentials' => array(
                    'key' => $CONFIG['AWS']['KEY'],
                    'secret' => $CONFIG['AWS']['SECRET'],
                )
            ]);

            $parameters = [
                'Bucket' => $file['s3files_bucket'],
                'Key' => $file['s3files_path'] . "/" . $file['s3files_filename'] . '.' . $file['s3files_extension'],
            ];
            if ($forceDownload) $parameters['ResponseContentDisposition'] = 'attachment; filename="' . $CONFIG['PROJECT_NAME'] . ' ' . $file['s3files_filename'] . '.' . $file['s3files_extension'] . '"';
            $cmd = $s3Client->getCommand('GetObject', $parameters);
            $request = $s3Client->createPresignedRequest($cmd, $file['expiry']);
            $presignedUrl = (string)$request->getUri();
            return $presignedUrl;
        }
    }
    function aTag($id) {
        if ($id == null) return null;
        if ($id <= 9999) return "A-" . sprintf('%04d', $id);
        else return "A-" . $id;
    }
    function reverseATag($tag) {
        //Reverse the process above, being sure to pick out any leading 0s
        $tag = strtolower($tag);
        $tag = str_replace("a-000",null,$tag);
        $tag = str_replace("a-00",null,$tag);
        $tag = str_replace("a-0",null,$tag);
        $tag = str_replace("a-",null,$tag);
        return $tag;
    }
    function notificationSettings($userid) {
        global $DBLIB,$CONFIG;
        $DBLIB->where("users_userid", $userid);
        $user = $DBLIB->getone("users",["users_email", "users_userid", "users_name1", "users_name2","users_notificationSettings"]);
        if (!$user) return false;
        $user["users_notificationSettings"] = json_decode($user["users_notificationSettings"],true);
        if (!$user["users_notificationSettings"]) $user["users_notificationSettings"] = [];
        $configReturn = [];
        foreach ($CONFIG['NOTIFICATIONS']['TYPES'] as $type) {
            $type['methodsUser'] = [];
            foreach ($type['methods'] as $method) {
                if ($type['canDisable']) {
                     foreach($user["users_notificationSettings"] as $individualSetting) {
                         if ($individualSetting['method'] == $method and $individualSetting['type'] == $type['id']) {
                             $type['methodsUser'][$method] = ($individualSetting['setting'] == "true" ? true : false);
                             break;
                         }
                     }
                     if (!isset($type['methodsUser'][$method])) $type['methodsUser'][$method] = $type['default'];
                } else {
                    $type['methodsUser'][$method] = true;
                }
            }
            $configReturn[$type['id']] = $type;
        }
        return ["userData" => $user,"settings"=>$configReturn];
    }
    function usersWatchingGroup($groupid) {
        global $DBLIB;
        if (!is_numeric($groupid)) return false;
        $DBLIB->where("FIND_IN_SET(" . $groupid. ", users_assetGroupsWatching)");
        $users = $DBLIB->get("users",null,["users_userid"]);
        $return = [];
        foreach ($users as $user) {
            array_push($return,$user['users_userid']);
        }
        return $return;
    }
}

$GLOBALS['bCMS'] = new bCMS;






function generateNewTag() {
    global $DBLIB;
    //Get highest current tag
    $DBLIB->orderBy("assets_tag", "DESC");
    $tag = $DBLIB->getone("assets", ["assets_tag"]);
    if ($tag) return intval($tag["assets_tag"])+1;
    else return 1;
}

$GLOBALS['STATUSES'] = [
    0 => [
        "name" => "Added to RMS",
        "description" => "Default",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#F5F5F5",
        "order" => 0,
        "assetsAvailable" => false,
        "class" => "info"
    ],
    1 => [
        "name" => "Targeted",
        "description" => "Being targeted as a lead",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#F5F5F5",
        "order" => 1,
        "assetsAvailable" => false,
        "class" => "info"
    ],
    2 => [
        "name" => "Quote Sent",
        "description" => "Waiting for client confirmation",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#ffdd99",
        "order" => 2,
        "assetsAvailable" => false,
        "class" => "warning"
    ],
    3 => [
        "name" => "Confirmed",
        "description" => "Booked in with client",
        "foregroundColour" => "#ffffff",
        "backgroundColour" => "#66ff66",
        "order" => 3,
        "assetsAvailable" => false,
        "class" => "success"
    ],
    4 => [
        "name" => "Prep",
        "description" => "Being prepared for dispatch" ,
        "foregroundColour" => "#000000",
        "backgroundColour" => "#ffdd99",
        "order" => 4,
        "assetsAvailable" => false,
        "class" => "success"
    ],
    5 => [
        "name" => "Dispatched",
        "description" => "Sent to client" ,
        "foregroundColour" => "#ffffff",
        "backgroundColour" => "#66ff66",
        "order" => 5,
        "assetsAvailable" => false,
        "class" => "primary"
    ],
    6 => [
        "name" => "Returned",
        "description" => "Waiting to be checked in ",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#ffdd99",
        "order" => 6,
        "assetsAvailable" => false,
        "class" => "primary"
    ],
    7 => [
        "name" => "Closed",
        "description" => "Pending move to Archive",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#F5F5F5",
        "order" => 7,
        "assetsAvailable" => false,
        "class" => "secondary"
    ],
    8 => [
        "name" => "Cancelled",
        "description" => "Event Cancelled",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#F5F5F5",
        "order" => 8,
        "assetsAvailable" => true,
        "class" => "danger"
    ],
    9 => [
        "name" => "Lead Lost",
        "description" => "Event Cancelled",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#F5F5F5",
        "order" => 9,
        "assetsAvailable" => true,
        "class" => "danger"
    ]
];
$GLOBALS['STATUSES-AVAILABLE'] = [];
foreach ($GLOBALS['STATUSES'] as $key => $status) {
    if ($status['assetsAvailable']) array_push($GLOBALS['STATUSES-AVAILABLE'], $key);
}
usort($GLOBALS['STATUSES'], function($a, $b) {
    return $a['order'] - $b['order'];
});

$GLOBALS['ASSETASSIGNMENTSTATUSES'] = [
    0 => ["name" => "None applicable"],
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
function assetLatestScan($assetid) {
    if ($assetid == null) return false;
    global $DBLIB;
    $DBLIB->orderBy("assetsBarcodesScans.assetsBarcodesScans_timestamp","DESC");
    $DBLIB->where("assetsBarcodes.assets_id",$assetid);
    $DBLIB->where("assetsBarcodes.assetsBarcodes_deleted",0);
    $DBLIB->join("assetsBarcodes","assetsBarcodes.assetsBarcodes_id=assetsBarcodesScans.assetsBarcodes_id");
    $DBLIB->join("locationsBarcodes","locationsBarcodes.locationsBarcodes_id=assetsBarcodesScans.locationsBarcodes_id","LEFT");
    $DBLIB->join("assets","assets.assets_id=assetsBarcodesScans.location_assets_id","LEFT");
    $DBLIB->join("assetTypes","assets.assetTypes_id=assetTypes.assetTypes_id","LEFT");
    $DBLIB->join("locations","locations.locations_id=locationsBarcodes.locations_id","LEFT");
    $DBLIB->join("users","users.users_userid=assetsBarcodesScans.users_userid");
    return $DBLIB->getone("assetsBarcodesScans",["assetsBarcodesScans.*","users.users_name1","users.users_name2","locations.locations_name","locations.locations_id","assets.assetTypes_id","assetTypes.assetTypes_name"]);

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
        global $AUTH;
        //Reset the data
        $this->projectid = $projectid;
        $this->data = [
            "projectsFinanceCache_equipmentSubTotal" =>new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),
            "projectsFinanceCache_equiptmentDiscounts" =>new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),
            "projectsFinanceCache_salesTotal" =>new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),
            "projectsFinanceCache_staffTotal" =>new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),
            "projectsFinanceCache_externalHiresTotal" =>new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),
            "projectsFinanceCache_paymentsReceived" =>new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),
            "projectsFinanceCache_value"=>new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),
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
                if ($key != 'projectsFinanceCache_mass') $value = $value->getAmount();
                if ($value != 0) $dataToUpload[$key] = $DBLIB->inc($value);
            }
            $dataToUpload['projectsFinanceCache_timestampUpdated'] = date("Y-m-d H:i:s");
            $dataToUpload['projectsFinanceCache_equiptmentTotal'] = $DBLIB->inc($this->data["projectsFinanceCache_equipmentSubTotal"]->subtract($this->data['projectsFinanceCache_equiptmentDiscounts'])->getAmount());
            $dataToUpload['projectsFinanceCache_grandTotal'] = $DBLIB->inc((($this->data["projectsFinanceCache_equipmentSubTotal"]->subtract($this->data['projectsFinanceCache_equiptmentDiscounts']))->add($this->data['projectsFinanceCache_salesTotal'],$this->data['projectsFinanceCache_staffTotal'],$this->data["projectsFinanceCache_externalHiresTotal"])->subtract($this->data['projectsFinanceCache_paymentsReceived']))->getAmount());
            $DBLIB->where("projects_id", $this->projectid);
            $DBLIB->orderBy("projectsFinanceCache_timestamp", "DESC");
            return $DBLIB->update("projectsFinanceCache", $dataToUpload, 1); //Update the most recent cache datapoint
        } else return true;
    }
    public function adjust($key,$value,$subtract = false) {
        if ($key == 'projectsFinanceCache_mass' and ($value !== 0 or $value !== null)) {
            $this->changesMade = true;
            if ($subtract) $value = -1*$value;
            $this->data[$key] += $value;
        } else {
            $this->changesMade = true;
            //It's a money object!
            if ($subtract) {
                $this->data[$key] = $this->data[$key]->subtract($value);
            } else {
                $this->data[$key] = $this->data[$key]->add($value);
            }
        }
    }
    public function adjustPayment($paymentType,$value,$subtract = false) {
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
        return $this->adjust($key,$value,$subtract);
    }
}