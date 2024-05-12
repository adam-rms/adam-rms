<?php
require_once __DIR__ . '/../../../api/notifications/notificationTypes.php';

/* USEFUL FUNCTIONS */
class bCMS
{
  function sanitizeString($var)
  {
    global $DBLIB;
    //Setup Sanitize String Function
    //$var = strip_tags($var);
    $var = htmlspecialchars($var, ENT_NOQUOTES);
    //$var = stripslashes($var);
    return $var;
  }
  function sanitizeStringMYSQL($var)
  {
    global $DBLIB;
    return $DBLIB->escape($this->sanitizeString($var));
  }
  function randomString($length = 10, $stringonly = false)
  { //Generate a random string
    $characters = 'abcdefghkmnopqrstuvwxyzABCDEFGHKMNOPQRSTUVWXYZ';
    if (!$stringonly) $characters .= '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }
  function cleanString($var)
  {
    //HTML Purification
    //$var = str_replace(array("\r", "\n"), '<br>', $var); //Replace newlines

    $config = HTMLPurifier_Config::createDefault();
    $config->set('Cache.DefinitionImpl', null);
    //$config->set('AutoFormat.Linkify', true);
    $purifier = new HTMLPurifier($config);
    return $purifier->purify($var); //NOTE THAT THIS REQUIRES THE USE OF PREPARED STATEMENTS AS IT'S NOT ESCAPED
  }
  function formatSize($bytes)
  {
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
  function modifyGet($array)
  {
    //Used to setup links that don't affect search terms etc.
    foreach ($array as $key => $value) {
      $_GET[$key] = $value;
    }
    return $_GET;
  }
  function auditLog($actionType = null, $table = null, $revelantData = null, $userid = null, $useridTo = null, $projectid = null, $targetid = null)
  { //Keep an audit trail of actions - $userid is this user, and $useridTo is who this action was done to if it was at all
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
  function s3StorageUsed($instancesid)
  {
    global $DBLIB;
    $DBLIB->where("s3files.instances_id", $instancesid);
    $DBLIB->where("(s3files_meta_deleteOn IS NULL)");
    $DBLIB->where("s3files_meta_physicallyStored", 1);
    return $DBLIB->getValue("s3files", "SUM(s3files_meta_size)");
  }
  function s3List($typeid, $subTypeid = false, $sort = 's3files_meta_uploaded', $sortOrder = 'ASC', $limit = null)
  {
    global $DBLIB, $CONFIG;
    $DBLIB->where("s3files_meta_type", $typeid);
    if ($subTypeid) $DBLIB->where("s3files_meta_subType", $subTypeid);
    $DBLIB->where("(s3files_meta_deleteOn >= '" . date("Y-m-d H:i:s") . "' OR s3files_meta_deleteOn IS NULL)"); //If the file is to be deleted soon or has been deleted don't let them download it
    $DBLIB->where("s3files_meta_physicallyStored", 1); //If we've lost the file or deleted it we can't actually let them download it
    $DBLIB->orderBy($sort, $sortOrder);
    return $DBLIB->get("s3files", $limit, ["s3files_id", "s3files_extension", "s3files_name", "s3files_meta_size", "s3files_meta_uploaded", "s3files_shareKey"]);
  }
  function s3DataUri($fileid)
  {
    /**
     * Returns a data URI for the file, upto a limit of 10MB. 
     * The PDF library used to generate PDFs from HTML requires a data URI for images, so this is used to generate them. It can accept file URLs but misses the cors preflight when sending the request so fails CORS in the browser.
     */
    $file = $this->s3Passthrough($fileid);
    if (!$file) return false;

    if ($file["type"] == "png") $type = "image/png";
    elseif ($file["type"] == "jpg") $type = "image/jpeg";
    elseif ($file["type"] == "jpeg") $type = "image/jpeg";
    elseif ($file["type"] == "jfif") $type = "image/jpeg";
    elseif ($file["type"] == "gif") $type = "image/gif";
    elseif ($file["type"] == "heic") $type = "image/heic";
    elseif ($file["type"] == "heif") $type = "image/heif";
    else return false; // Only supports images

    return 'data: ' . $type . ';base64,' . base64_encode($file['data']);
  }
  function s3Passthrough($fileid)
  {
    global $DBLIB;
    $DBLIB->where("s3files_id", intval($fileid));
    $DBLIB->where("(s3files_meta_deleteOn >= '" . date("Y-m-d H:i:s") . "' OR s3files_meta_deleteOn IS NULL)"); //If the file is to be deleted soon or has been deleted don't let them download it
    $DBLIB->where("s3files_meta_physicallyStored", 1); //If we've lost the file or deleted it we can't actually let them download it
    $DBLIB->where("s3files_meta_size", 10485760, "<="); //Limit files to 10mb for this function
    $file = $DBLIB->getone("s3files", ["s3files_extension", "s3files_id"]);
    if (!$file) return false;

    $url = $this->s3URL($file["s3files_id"], false);
    if (!$url) return false;

    $data = file_get_contents($url);
    if (!$data) return false;

    return [
      "data" => $data,
      "type" => $file["s3files_extension"],
      "url" => $url
    ];
  }
  function s3URL($fileid, $forceDownload = false, $expire = '+10 minutes', $shareKey = false)
  {
    global $DBLIB, $CONFIG, $AUTH, $CONFIGCLASS;
    /*
         * File interface for Amazon AWS S3.
         *  Parameters
         *      f (required) - the file id as specified in the database
         *      d (optional, default false) - should a download be forced or should it be displayed in the browser? (if set it will download)
         *      e (optional, default 1 minute) - when should the link expire? Must be a string describing how long in words basically. If this file type has security features then it will default to 1 minute.
         */
    $fileid = $this->sanitizeString($fileid);
    if (strlen($fileid) < 1) return false;
    $DBLIB->where("s3files_id", $fileid);
    $DBLIB->where("(s3files_meta_deleteOn >= '" . date("Y-m-d H:i:s") . "' OR s3files_meta_deleteOn IS NULL)"); //If the file is to be deleted soon or has been deleted don't let them download it
    $DBLIB->where("s3files_meta_physicallyStored", 1); //If we've lost the file or deleted it we can't actually let them download it
    $file = $DBLIB->getone("s3files");
    if (!$file) return false;
    if ($expire == null or $expire === false) $expire = '+1 minute';
    $file['expiry'] = $expire;

    $instanceIgnore = false;
    $secure = true;
    // This list is also used to populate the files deletion suggestor
    switch ($file['s3files_meta_type']) {
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
        $secure = true;
        $instanceIgnore = false;
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
    if ($CONFIGCLASS->get('AWS_CLOUDFRONT_ENABLED') === 'Enabled') {
      // Create a CloudFront Client to sign the string
      $CloudFrontClient = new Aws\CloudFront\CloudFrontClient([
        'profile' => 'default',
        'version' => '2014-11-06',
        'region' => 'us-east-2'
      ]);

      $ResponseContentDisposition = "?response-content-disposition=" . rawurlencode(
        ($forceDownload ? 'attachment' : 'inline') . '; filename=' . utf8_encode(preg_replace('/[^A-Za-z0-9 _\-]/', '_', $file['s3files_name']) . '.' . $file['s3files_extension'])
      );

      $signedUrlCannedPolicy = $CloudFrontClient->getSignedUrl([
        'url' => $CONFIGCLASS->get("AWS_CLOUDFRONT_ENDPOINT") . "/" . $file['s3files_path'] . "/" . $file['s3files_filename'] . '.' . $file['s3files_extension'] . $ResponseContentDisposition,
        'expires' => strtotime($file['expiry']),
        'private_key' => str_replace(["BEGIN\nRSA\nPRIVATE\nKEY", "END\nRSA\nPRIVATE\nKEY"], ["BEGIN RSA PRIVATE KEY", "END RSA PRIVATE KEY"], str_replace(" ", "\n", $CONFIGCLASS->get('AWS_CLOUDFRONT_PRIVATEKEY'))),
        'key_pair_id' => $CONFIGCLASS->get('AWS_CLOUDFRONT_KEYPAIRID')
      ]);
      return $signedUrlCannedPolicy;
    } else {
      //Download direct from S3
      $s3Client = new Aws\S3\S3Client([
        'region' => $CONFIGCLASS->get('AWS_S3_REGION'),
        'endpoint' => $CONFIGCLASS->get('AWS_S3_BROWSER_ENDPOINT'),
        'use_path_style_endpoint' => $CONFIGCLASS->get('AWS_S3_ENDPOINT_PATHSTYLE') === 'Enabled',
        'version' => 'latest',
        'credentials' => array(
          'key' => $CONFIGCLASS->get('AWS_S3_KEY'),
          'secret' => $CONFIGCLASS->get('AWS_S3_SECRET'),
        )
      ]);

      $parameters = [
        'Bucket' => $CONFIGCLASS->get('AWS_S3_BUCKET'),
        'Key' => $file['s3files_path'] . "/" . $file['s3files_filename'] . '.' . $file['s3files_extension'],
      ];
      $parameters['ResponseContentDisposition'] = ($forceDownload ? 'attachment' : 'inline') . '; filename="' . preg_replace('/[^A-Za-z0-9 _\-]/', '_', $file['s3files_name']) . '.' . $file['s3files_extension'] . '"';

      $cmd = $s3Client->getCommand('GetObject', $parameters);
      $request = $s3Client->createPresignedRequest($cmd, $file['expiry']);
      $presignedUrl = (string)$request->getUri();
      return $presignedUrl;
    }
  }
  function aTag($id)
  {
    //This is an old function we no longer use - kept to save refactoring
    return $id;
  }
  function notificationSettings($userid)
  {
    global $DBLIB, $NOTIFICATIONTYPES;
    $DBLIB->where("users_userid", $userid);
    $user = $DBLIB->getone("users", ["users_email", "users_userid", "users_name1", "users_name2", "users_notificationSettings"]);
    if (!$user) return false;
    $user["users_notificationSettings"] = json_decode($user["users_notificationSettings"], true);
    if (!$user["users_notificationSettings"]) $user["users_notificationSettings"] = [];
    $configReturn = [];
    foreach ($NOTIFICATIONTYPES['TYPES'] as $type) {
      $type['methodsUser'] = [];
      foreach ($type['methods'] as $method) {
        if ($type['canDisable']) {
          foreach ($user["users_notificationSettings"] as $individualSetting) {
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
    return ["userData" => $user, "settings" => $configReturn];
  }
  function usersWatchingGroup($groupid)
  {
    global $DBLIB;
    if (!is_numeric($groupid)) return false;
    $DBLIB->where("FIND_IN_SET(" . $groupid . ", users_assetGroupsWatching)");
    $users = $DBLIB->get("users", null, ["users_userid"]);
    $return = [];
    foreach ($users as $user) {
      array_push($return, $user['users_userid']);
    }
    return $return;
  }
  function getVersionNumber()
  {
    if (getenv('VERSION_NUMBER')) {
      if (strlen(getenv('VERSION_NUMBER')) > 7) return substr(getenv('VERSION_NUMBER'), 0, 7);
      else return getenv('VERSION_NUMBER');
    } else if (file_exists(__DIR__ . '/../../../version/TAG.txt')) {
      return file_get_contents(__DIR__ . '/../../../version/TAG.txt');
    } else if (file_exists(__DIR__ . '/../../../version/COMMIT.txt')) {
      return file_get_contents(__DIR__ . '/../../../version/COMMIT.txt');
    } else {
      return "Version Unknown";
    }
  }
  function instanceHasUserCapacity($instanceid)
  {
    global $DBLIB;
    $DBLIB->where("instances_id", $instanceid);
    $userCapacity = $DBLIB->getvalue("instances", "instances_userLimit");
    $DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid", "LEFT");
    $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id", "LEFT");
    $DBLIB->where("instances_id", $instanceid);
    $DBLIB->where("userInstances.userInstances_deleted",  0);
    $DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
    $userUsed = $DBLIB->getValue("users", "COUNT(users.users_userid)");
    if ($userCapacity > 0 and $userUsed >= $userCapacity)
      return false;
    return true;
  }
  function instanceHasProjectCapacity($instanceid)
  {
    global $DBLIB;
    $DBLIB->where("instances_id", $instanceid);
    $projectCapacity = $DBLIB->getvalue("instances", "instances_projectLimit");

    $DBLIB->where("projects.instances_id", $instanceid);
    $DBLIB->where("projects.projects_deleted", 0);
    $projectsUsed = $DBLIB->getValue("projects", "COUNT(projects.projects_id)");

    if ($projectCapacity > 0 and $projectsUsed >= $projectCapacity)
      return false;
    return true;
  }
  function instanceHasAssetCapacity($instanceid)
  {
    global $DBLIB;
    $DBLIB->where("instances_id", $instanceid);
    $projectCapacity = $DBLIB->getvalue("instances", "instances_assetLimit");

    $DBLIB->where("instances_id", $instanceid);
    $DBLIB->where("assets_deleted", 0);
    $assetUsed = $DBLIB->getValue("assets", "COUNT(assets_id)");

    if ($projectCapacity > 0 and $assetUsed >= $projectCapacity)
      return false;
    return true;
  }
}
