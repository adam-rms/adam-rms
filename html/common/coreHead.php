<?php
require_once __DIR__ . '/config.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\CloudFront\CloudFrontClient;
use Aws\Exception\AwsException;

if (!$CONFIG['DEV']) {
    Sentry\init([
        'dsn' => $CONFIG['ERRORS']['SENTRY'],
        'traces_sample_rate' => 0.1, //Capture 10% of pageloads for perforamnce monitoring
        'release' => $CONFIG['VERSION']['TAG'] . "." . $CONFIG['VERSION']['COMMIT'],
        'sample_rate' => 1.0,
    ]);
}

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
        //$config->set('AutoFormat.Linkify', true);
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($var); //NOTE THAT THIS REQUIRES THE USE OF PREPARED STATEMENTS AS IT'S NOT ESCAPED
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
        return $DBLIB->get("s3files", $limit, ["s3files_id", "s3files_extension", "s3files_name","s3files_meta_size", "s3files_meta_uploaded","s3files_shareKey"]);
    }
    function s3URL($fileid, $size = "comp", $forceDownload = false, $expire = '+10 minutes', $shareKey = false) {
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
        if ($file['s3files_compressed'] == 1) {
            //If we have a compressed version of this file opt to use it!
            switch ($size) {
                case "tiny":
                    $file['s3files_filename'] .= '_tiny';
                    break;
                case "small":
                    $file['s3files_filename'] .= '_small';
                    break;
                case "medium":
                    $file['s3files_filename'] .= '_medium';
                    break;
                case "large":
                    $file['s3files_filename'] .= '_large';
                    break;
                case "comp":
                    $file['s3files_filename'] .= '_comp';
                    break;
                case "full":
                    break;
                default:
                    $file['s3files_filename'] .= '_comp';
                    break;
            }
        }
        if ($expire == null or $expire === false) $expire = '+1 minute';
        $file['expiry'] = $expire;

        $instanceIgnore = false;
        $secure = true;
        switch ($file['s3files_meta_type']) {
            case 1:
                $instanceIgnore = true;
                //This is a user thumbnail
                break;
            case 2:
                $instanceIgnore = true;
                $secure = false; //Needs to be viewed on the public site
                // Asset type thumbnail
                break;
            case 3:
                // Asset type file
                break;
            case 4:
                // Asset file
                break;
            case 5:
                $instanceIgnore = true;
                $secure = false;
                // Instance thumbnail
                break;
            case 6:
                // Instance file
                break;
            case 7:
                //Project file
                break;
            case 8:
                // Maintenance job file
                break;
            case 9:
                $instanceIgnore = true;
                // User thumbnail
                break;
            case 10:
                $instanceIgnore = true;
                $secure = false;
                //Instance email thumbnail
                break;
            case 11:
                // Location file
                break;
            case 12:
                //Module thumbnail
                break;
            case 13:
                //Module step image
                break;
            case 14:
                //Payment file attachment
                break;
            case 15:
                //Public file
                $secure = false;
                $instanceIgnore = true;
                break;
            case 16:
                //Public homepage content image
                $secure = false;
                $instanceIgnore = true;
                break;
            case 17:
                //Public homepage header image
                $secure = false;
                $instanceIgnore = true;
                break;
            case 18:
                //Vacant role application attachment
                break;
            case 19:
                //CMS image
                $DBLIB->where("cmsPages_deleted",0);
                $DBLIB->where("cmsPages_id",$file['s3files_meta_subType']);
                $page = $DBLIB->getone("cmsPages",["cmsPages_showPublic"]);
                if ($page and $page['cmsPages_showPublic'] == 1) {
                    //Images on that page should be public on the web
                    $secure = false;
                    $instanceIgnore = true;
                } else {
                    $secure = true;
                    $instanceIgnore = false;
                }
                break;
            case 20:
                //Project invoice
                break;
            case 21:
                //Project quote
                break;
            default:
                //There are no specific requirements for this file so not to worry.
                break;
        }
        if ($shareKey and ($shareKey == hash('sha256', $file['s3files_shareKey'] . "|" . $file['s3files_id']))) $secure = false; //File has been shared publicly, and the key matches

        if ($secure and !$GLOBALS['AUTH']->login) return false;
        elseif ($secure and !$instanceIgnore and $file["instances_id"] != $AUTH->data['instance']['instances_id']) return false;

        //Generate the url

        if ($CONFIG['AWS']["CLOUDFRONT"]['ENABLED']) {
            // Create a CloudFront Client to sign the string
            $CloudFrontClient = new Aws\CloudFront\CloudFrontClient([
                'profile' => 'default',
                'version' => '2014-11-06',
                'region' => 'us-east-2'
            ]);

            $ResponseContentDisposition = "?response-content-disposition=" . rawurlencode(
                ($forceDownload ? 'attachment' : 'inline') . '; filename=' . utf8_encode(preg_replace('/[^A-Za-z0-9 _\-]/', '_', $file['s3files_name']) . '.' . $file['s3files_extension']));

            $signedUrlCannedPolicy = $CloudFrontClient->getSignedUrl([
                'url' => $file['s3files_cdn_endpoint'] . "/" . $file['s3files_path'] . "/" . $file['s3files_filename'] . '.' . $file['s3files_extension'] . $ResponseContentDisposition,
                'expires' => strtotime($file['expiry']),
                'private_key' => $CONFIG['AWS']["CLOUDFRONT"]["PRIVATEKEY"],
                'key_pair_id' => $CONFIG['AWS']["CLOUDFRONT"]["KEYPAIRID"]
            ]);
            return $signedUrlCannedPolicy;
        } else {
            //Download direct from S3
            $s3Client = new Aws\S3\S3Client([
                'region' => $file["s3files_region"],
                'endpoint' => ($file['s3files_cdn_endpoint'] ? $file['s3files_cdn_endpoint'] : "https://" . $file["s3files_endpoint"]),
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
            $parameters['ResponseContentDisposition'] = ($forceDownload ? 'attachment' : 'inline') . '; filename="' . preg_replace('/[^A-Za-z0-9 _\-]/', '_', $file['s3files_name']) . '.' . $file['s3files_extension'] . '"';

            $cmd = $s3Client->getCommand('GetObject', $parameters);
            $request = $s3Client->createPresignedRequest($cmd, $file['expiry']);
            $presignedUrl = (string)$request->getUri();
            return $presignedUrl;
        }
    }
    function aTag($id) {
        //This is an old function we no longer use - kept to save refactoring
        return $id;
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
    function deepSearch($instanceid=false,$page=1,$pageLimit=20,$sort="alphabet-a",$category=null,$keyword=[],$manufacturer=false,$group=false,$showLinked=false,$showArchived=false,$dateStart = false,$dateEnd = false,$tags=[]) {
        global $DBLIB,$AUTH;
        $scriptStartTime = microtime (true);
        $DBLIB->setTrace(true, $_SERVER['SERVER_ROOT']);
        $SEARCH = [
            "INSTANCE_ID" => $instanceid,
            "PAGE" =>  $page ? intval($page) : 1,
            "PAGE_LIMIT" => $pageLimit ? intval($pageLimit) : 20,
            "TERMS" => [
                "CATEGORY" => $category,
                "KEYWORDS" => (is_array($keyword)) ? $keyword : [],
                "MANUFACTURER" => $manufacturer,
                "GROUPS" => $group ?: false,
                "DATE-START" => $dateStart,
                "DATE-END" => $dateEnd,
                "SORT" => $sort,
                "TAGS" => (is_array($tags)) ? $tags : [],
            ],
            "SETTINGS" => [
                "SHOWLINKED" => $showLinked,
                "SHOWARCHIVED" => $showArchived
            ]
        ];
        $RETURN = [
            "PAGINATION" => [
                "PAGE" => $SEARCH['PAGE']
            ],
            "ASSETS" => [],
            "PROJECT" => [
                "ID" => false
            ]
        ];

        //Evaluate instance id
        if ($SEARCH['INSTANCE_ID'] == null) {
            if ($AUTH->login) $SEARCH['INSTANCE_ID'] = $AUTH->data['instance']['instances_id'];
            else return false;
        }
        $DBLIB->where("instances_id",$SEARCH['INSTANCE_ID']);
        $DBLIB->where("instances_deleted",0);
        $SEARCH['INSTANCE'] = $DBLIB->getone("instances",['instances_publicConfig','instances_id','instances_config_currency']);
        if (!$SEARCH['INSTANCE']) return false;
        $SEARCH['INSTANCE']['instances_publicConfig'] = json_decode($SEARCH['INSTANCE']['instances_publicConfig'],true);
        if (!$SEARCH['INSTANCE']['instances_publicConfig']['enableAssets'] and !$AUTH->login) return false;

        //Evaluate dates
        if ($dateStart and $dateEnd and ($SEARCH['INSTANCE']['instances_publicConfig']['enableAssetAvailability'] or $AUTH->login)) {
            $dateStart = strtotime($dateStart);
            $dateEnd = strtotime($dateEnd);
            if ($dateEnd <= $dateStart) {
                $dateStart = false;
                $dateEnd = false;
            }
        } elseif ($AUTH->login and $AUTH->data['users_selectedProjectID'] != null and $AUTH->instancePermissionCheck(31)) {
            $DBLIB->where("projects_id",$AUTH->data['users_selectedProjectID']);
            $DBLIB->where("projects.instances_id IN (" . implode(",", $AUTH->data['instance_ids']) . ")"); //Duplicated elsewhere
            $DBLIB->where("projects.projects_deleted", 0);
            $DBLIB->where("projects_dates_deliver_start",NULL,"IS NOT");
            $DBLIB->where("projects_dates_deliver_end",NULL,"IS NOT");
            $thisProject = $DBLIB->getone("projects",["projects_dates_deliver_start","projects_dates_deliver_end"]);
            if (!$thisProject) {
                $dateStart = false;
                $dateEnd = false;
            } else {
                $dateStart = strtotime($thisProject['projects_dates_deliver_start']);
                $dateEnd = strtotime($thisProject['projects_dates_deliver_end']);
                $RETURN['PROJECT']['ID'] = $AUTH->data['users_selectedProjectID'];
            }
        } else {
            $dateStart = false;
            $dateEnd = false;
        }
        $RETURN['PROJECT']['DATESTART'] = $dateStart;
        $RETURN['PROJECT']['DATEEND'] = $dateEnd;
        
        //**START CHONKY QUERY**

        //Evaluate categories
        $DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
        $DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
        if ($SEARCH['TERMS']['CATEGORY']) $DBLIB->where('assetTypes.assetCategories_id', $SEARCH['TERMS']['CATEGORY'], 'IN');

        //Evaluate manufacturers
        $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
        if ($SEARCH['TERMS']['MANUFACTURER']) $DBLIB->where('manufacturers.manufacturers_id',$SEARCH['TERMS']['MANUFACTURER'], 'IN');

        //Sorting
        $sortArray = explode("-",$SEARCH['TERMS']['SORT']);
        if (count($sortArray) == 2) {
            if ($sortArray[0] == "price") $DBLIB->orderBy("assetTypes.assetTypes_weekRate", ($sortArray[1] == "a" ? "ASC" : "DESC"));
            elseif ($sortArray[0] == "value") $DBLIB->orderBy("assetTypes.assetTypes_value", ($sortArray[1] == "a" ? "ASC" : "DESC"));
            elseif ($sortArray[0] == "alphabet") $DBLIB->orderBy("assetTypes.assetTypes_name", ($sortArray[1] == "a" ? "ASC" : "DESC"));
            elseif ($sortArray[0] == "mass") $DBLIB->orderBy("assetTypes.assetTypes_mass", ($sortArray[1] == "a" ? "ASC" : "DESC"));
            elseif ($sortArray[0] == "date") $DBLIB->orderBy("assetTypes.assetTypes_inserted", ($sortArray[1] == "a" ? "ASC" : "DESC"));
            else $DBLIB->orderBy("assetTypes.assetTypes_name", "ASC"); //Default
        } else $DBLIB->orderBy("assetTypes.assetTypes_name", "ASC");

        $DBLIB->orderBy("assetTypes.assetTypes_name", "ASC");

        //Keywords
        if (count($SEARCH['TERMS']['KEYWORDS']) > 0) {
            $thisWhere = false;
            $thisValues = [];
            foreach ($SEARCH['TERMS']['KEYWORDS'] as $word) {
                if ($word != null) {
                    if ($thisWhere != false) $thisWhere .= ' OR ';
                    else $thisWhere = "(";
                    $thisWhere .= "manufacturers.manufacturers_name LIKE ? OR assetTypes.assetTypes_description LIKE ? OR assetTypes.assetTypes_name LIKE ?";
                    array_push($thisValues,'%' . $word . '%','%' . $word . '%','%' . $word . '%');
                }
            }
            $DBLIB->where($thisWhere . ")",$thisValues);
        }


        //Limit the assets correctly
        $subQuery = $DBLIB->subQuery();
        $subQuery->where("assets.instances_id",$SEARCH['INSTANCE_ID']);
        $subQuery->where("assets_deleted",0);
        if (!$SEARCH['SETTINGS']['SHOWARCHIVED']) $subQuery->where ("(assets.assets_endDate IS NULL OR assets.assets_endDate >= '" . date ("Y-m-d H:i:s") . "')");

        if ($SEARCH['TERMS']['GROUPS']) {
            $thisWhere = false;
            $thisValues = [];

            foreach ($SEARCH['TERMS']['GROUPS'] as $group) {
                if ($group != null) {
                    if ($thisWhere != false) $thisWhere .= ' OR ';
                    else $thisWhere = "(";
                    $thisWhere .= "FIND_IN_SET(?, assets.assets_assetGroups)";
                    array_push($thisValues,intval($group));
                }
            }
            if ($thisWhere) $subQuery->where($thisWhere . ")",$thisValues);
        }
        if (!$SEARCH['SETTINGS']['SHOWLINKED']) $subQuery->where ("assets.assets_linkedTo", NULL, 'IS');
        if ($SEARCH['TERMS']['TAGS']) {
            $thisWhere = false;
            $thisValues = [];
            foreach ($SEARCH['TERMS']['TAGS'] as $word) {
                if ($word != null) {
                    if ($thisWhere != false) $thisWhere .= ' OR ';
                    else $thisWhere = "(";
                    $thisWhere .= "assets.assets_tag LIKE ?";
                    array_push($thisValues,'%' . $word . '%');
                }
            }
            if ($thisWhere) $subQuery->where($thisWhere . ")",$thisValues);
        }
        $subQuery->groupBy ("assetTypes_id");
        $subQuery->get ("assets", null, "assetTypes_id");
        $DBLIB->where ("assetTypes_id", $subQuery, 'in');

        //The actual select
        $DBLIB->pageLimit = $SEARCH["PAGE_LIMIT"];
        $DBLIB->where("(assetTypes.instances_id IS NULL OR assetTypes.instances_id = ?)",[$SEARCH['INSTANCE_ID']]);
        $assets = $DBLIB->arraybuilder()->paginate('assetTypes', $SEARCH["PAGE"], ["assetTypes.*", "manufacturers.*", "assetCategories.*", "assetCategoriesGroups_name"]);
        $RETURN['PAGINATION']['TOTAL-PAGES'] = $DBLIB->totalPages;
        $RETURN['PAGINATION']['COUNT'] = $DBLIB->totalCount;
        $RETURN['PAGINATION']['OFFSET'] = $SEARCH["PAGE_LIMIT"]*($SEARCH["PAGE"]-1);
        foreach ($assets as $asset) {
            $DBLIB->where("assets.assetTypes_id", $asset['assetTypes_id']);
            $DBLIB->where("assets.instances_id",$SEARCH['INSTANCE_ID']);
            $DBLIB->where("assets_deleted",0);
            if (!$SEARCH['SETTINGS']['SHOWARCHIVED']) $DBLIB->where ("(assets.assets_endDate IS NULL OR assets.assets_endDate >= '" . date ("Y-m-d H:i:s") . "')");
            if ($SEARCH['TERMS']['GROUPS']) {
                $thisWhere = false;
                $thisValues = [];

                foreach ($SEARCH['TERMS']['GROUPS'] as $group) {
                    if ($group != null) {
                        if ($thisWhere != false) $thisWhere .= ' OR ';
                        else $thisWhere = "(";
                        $thisWhere .= "FIND_IN_SET(?, assets.assets_assetGroups)";
                        array_push($thisValues,intval($group));
                    }
                }
                if ($thisWhere) $DBLIB->where($thisWhere . ")",$thisValues);
            }
            if (!$SEARCH['SETTINGS']['SHOWLINKED']) $DBLIB->where ("assets.assets_linkedTo", NULL, 'IS');
            if ($SEARCH['TERMS']['TAGS']) {
                $thisWhere = false;
                $thisValues = [];
                foreach ($SEARCH['TERMS']['TAGS'] as $word) {
                    if ($word != null) {
                        if ($thisWhere != false) $thisWhere .= ' OR ';
                        else $thisWhere = "(";
                        $thisWhere .= "assets.assets_tag LIKE ?";
                        array_push($thisValues,'%' . $word . '%');
                    }
                }
                if ($thisWhere) $DBLIB->where($thisWhere . ")",$thisValues);
            }
            $DBLIB->orderBy("assets.assets_tag", "ASC");
            $assetTags = $DBLIB->get("assets", null, ["assets_id", "assets_notes","assets_tag","asset_definableFields_1","asset_definableFields_2","asset_definableFields_3","asset_definableFields_4","asset_definableFields_5","asset_definableFields_6","asset_definableFields_7","asset_definableFields_8","asset_definableFields_9","asset_definableFields_10","assets_dayRate","assets_weekRate","assets_value","assets_mass","assets_endDate"]);
            if (!$assetTags) continue;
            $asset['count'] = count($assetTags);
            $asset['fields'] = explode(",", $asset['assetTypes_definableFields']);
            $asset['thumbnail'] = $this->s3List(2, $asset['assetTypes_id'],'s3files_meta_uploaded','ASC',1);
            $asset['tags'] = [];
            foreach ($assetTags as $tag) {
                if ($dateStart and $dateEnd) {
                    //Check availability
                    $DBLIB->where("assets_id", $tag['assets_id']);
                    $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
                    $DBLIB->where("(projects.projects_status NOT IN (" . implode(",", $GLOBALS['STATUSES-AVAILABLE']) . ")" . ($RETURN['PROJECT']['ID'] ? "OR (projects.projects_id = '" . $RETURN['PROJECT']['ID'] . "')" : '') . ")");
                    $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
                    $DBLIB->where("projects.projects_deleted", 0);
                    $DBLIB->where("((projects_dates_deliver_start >= '" . date ("Y-m-d H:i:s",$dateStart)  . "' AND projects_dates_deliver_start <= '" . date ("Y-m-d H:i:s",$dateEnd) . "') OR (projects_dates_deliver_end >= '" . date ("Y-m-d H:i:s",$dateStart) . "' AND projects_dates_deliver_end <= '" . date ("Y-m-d H:i:s",$dateEnd) . "') OR (projects_dates_deliver_end >= '" . date ("Y-m-d H:i:s",$dateEnd) . "' AND projects_dates_deliver_start <= '" . date ("Y-m-d H:i:s",$dateStart) . "'))");
                    $tag['assignment'] = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.projects_id", "projects.projects_name"]);
                }
                if (!$SEARCH['INSTANCE']['instances_publicConfig']['enableAssetsPrices'] and !$AUTH->login) {
                    $tag['assets_dayRate'] = null;
                    $tag['assets_weekRate'] = null;
                } if (!$SEARCH['INSTANCE']['instances_publicConfig']["enableAssetsValues"] and !$AUTH->login) $tag['assets_value'] = null;
                if (!$SEARCH['INSTANCE']['instances_publicConfig']["enableAssetNotes"] and !$AUTH->login) $tag['assets_notes'] = null;
                $tag['flagsblocks'] = assetFlagsAndBlocks($tag['assets_id']);
                $asset['tags'][] = $tag;
            }
            if (!$SEARCH['INSTANCE']['instances_publicConfig']['enableAssetsPrices'] and !$AUTH->login) {
                $asset['assetTypes_weekRate'] = null;
                $asset['assetTypes_dayRate'] = null;
            } if (!$SEARCH['INSTANCE']['instances_publicConfig']["enableAssetsValues"] and !$AUTH->login) $asset['assetTypes_value'] = null;
            if (!$SEARCH['INSTANCE']['instances_publicConfig']["enableAssetDescriptions"] and !$AUTH->login) $asset['assetTypes_description'] = null;
            $RETURN['ASSETS'][] = $asset;
        }
        $RETURN['SEARCH'] = $SEARCH;
        $RETURN['SPEED'] = microtime (true)-$scriptStartTime;
        return $RETURN;
    }
}

$GLOBALS['bCMS'] = new bCMS;






function generateNewTag() {
    global $DBLIB;
    //Get highest current tag
    $DBLIB->orderBy("assets_tag", "DESC");
    $DBLIB->where ("assets_tag", 'A-%', 'like');
    $tag = $DBLIB->getone("assets", ["assets_tag"]);
    if ($tag) {
        if (is_numeric(str_replace("A-","",$tag["assets_tag"]))) {
            $value = intval(str_replace("A-","",$tag["assets_tag"]))+1;
            if ($value <= 9999) $value = sprintf('%04d', $value);
            return "A-" . $value;
        } else return "A-0001";
    } else return "A-0001";
}

$GLOBALS['STATUSES'] = [
    0 => [
        "name" => "Added to RMS",
        "description" => "Default",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#F5F5F5",
        "order" => 0,
        "assetsAvailable" => false,
        "isCancelled" => false,
        "class" => "info"
    ],
    1 => [
        "name" => "Targeted",
        "description" => "Being targeted as a lead",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#F5F5F5",
        "order" => 1,
        "assetsAvailable" => false,
        "isCancelled" => false,
        "class" => "info"
    ],
    2 => [
        "name" => "Quote Sent",
        "description" => "Waiting for client confirmation",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#ffdd99",
        "order" => 2,
        "assetsAvailable" => false,
        "isCancelled" => false,
        "class" => "warning"
    ],
    3 => [
        "name" => "Confirmed",
        "description" => "Booked in with client",
        "foregroundColour" => "#ffffff",
        "backgroundColour" => "#66ff66",
        "order" => 3,
        "assetsAvailable" => false,
        "isCancelled" => false,
        "class" => "success"
    ],
    4 => [
        "name" => "Prep",
        "description" => "Being prepared for dispatch" ,
        "foregroundColour" => "#000000",
        "backgroundColour" => "#ffdd99",
        "order" => 4,
        "assetsAvailable" => false,
        "isCancelled" => false,
        "class" => "success"
    ],
    5 => [
        "name" => "Dispatched",
        "description" => "Sent to client" ,
        "foregroundColour" => "#ffffff",
        "backgroundColour" => "#66ff66",
        "order" => 5,
        "assetsAvailable" => false,
        "isCancelled" => false,
        "class" => "primary"
    ],
    6 => [
        "name" => "Returned",
        "description" => "Waiting to be checked in ",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#ffdd99",
        "order" => 6,
        "assetsAvailable" => false,
        "isCancelled" => false,
        "class" => "primary"
    ],
    7 => [
        "name" => "Closed",
        "description" => "Pending move to Archive",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#F5F5F5",
        "order" => 7,
        "assetsAvailable" => false,
        "isCancelled" => false,
        "class" => "secondary"
    ],
    8 => [
        "name" => "Cancelled",
        "description" => "Event Cancelled",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#F5F5F5",
        "order" => 8,
        "assetsAvailable" => true,
        "isCancelled" => true,
        "class" => "danger"
    ],
    9 => [
        "name" => "Lead Lost",
        "description" => "Event Cancelled",
        "foregroundColour" => "#000000",
        "backgroundColour" => "#F5F5F5",
        "order" => 9,
        "assetsAvailable" => true,
        "isCancelled" => true,
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

$GLOBALS['MAINTENANCEJOBPRIORITIES'] = [
    1 => ["class" => "danger","id" => 1,"text" => "Emergency"],
    2 => ["class" => "danger", "id" => 2, "text" => "Business Critical"],
    3 => ["class" => "danger", "id" => 3, "text" => "Urgent"],
    4 => ["class" => "danger", "id" => 4, "text" => "Routine - High"],
    5 => ["class" => "warning", "id" => 5, "text" => "Routine - Medium", "default"=>true],
    6 => ["class" => "warning", "id" => 6, "text" => "Routine - Low"],
    7 => ["class" => "warning", "id" => 7, "text" => "Monthly-cycle Maintenance"],
    8 => ["class" => "success", "id" => 8, "text" => "Annual-cycle Maintenance"],
    9 => ["class" => "success", "id" => 9, "text" => "Long Term"],
    10 => ["class" => "info", "id" => 10, "text" => "Log only"]
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
use Money\Money;
use Money\Currency;
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


$PAGEDATA = array('CONFIG' => $CONFIG, 'BODY' => true);
$PAGEDATA['STATUSES'] = $GLOBALS['STATUSES'];
$PAGEDATA['STATUSESAVAILABLE'] = $GLOBALS['STATUSES-AVAILABLE'];
$PAGEDATA['MAINTENANCEJOBPRIORITIES'] = $GLOBALS['MAINTENANCEJOBPRIORITIES'];


require_once __DIR__ . '/libs/twig.php';
