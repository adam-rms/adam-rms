<?php
require_once __DIR__ . '/config.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

//GLOBALS STUFF - DON'T CHANGE
/*
function errorHandler() {
    if (error_get_last() and error_get_last()['type'] == '1') {
        global $CONFIG;
        try {
            header('Location: ' . $CONFIG['ERRORS']['URL'] . '?e=' . urlencode(error_get_last()['message']) . '&return=' . urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"));
        } catch (Exception $e) {
            die('<meta http-equiv="refresh" content="0; url=' . $CONFIG['ROOTURL'] . '/error/?e=' . urlencode(error_get_last()['message']) . '&return=' . urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") . '" />');
        }
    }
}
//set_error_handler('errorHandler');
$CONFIG['ERRORS']['SENTRY-CLIENT']['MAIN'] = new Raven_Client($CONFIG['ERRORS']['SENTRY']);
$CONFIG['ERRORS']['SENTRY-CLIENT']['MAIN']->setRelease($CONFIG['VERSION']['TAG'] . "." . $CONFIG['VERSION']['COMMIT']);
$CONFIG['ERRORS']['SENTRY-CLIENT']['HANDLER'] = new Raven_ErrorHandler($CONFIG['ERRORS']['SENTRY-CLIENT']['MAIN']);
$CONFIG['ERRORS']['SENTRY-CLIENT']['HANDLER']->registerExceptionHandler();
$CONFIG['ERRORS']['SENTRY-CLIENT']['HANDLER']->registerErrorHandler();
$CONFIG['ERRORS']['SENTRY-CLIENT']['HANDLER']->registerShutdownFunction();
register_shutdown_function('errorHandler');

*/

try {
    //session_set_cookie_params(0, '/', '.' . $_SERVER['SERVER_NAME']); //Fix for subdomain bug
    session_set_cookie_params(43200); //12hours
    session_start(); //Open up the session
} catch (Exception $e) {
    //Do Nothing
}
/* DATBASE CONNECTIONS */
$CONN = new mysqli($CONFIG['DB_HOSTNAME'], $CONFIG['DB_USERNAME'], $CONFIG['DB_PASSWORD'], $CONFIG['DB_DATABASE']);
if ($CONN->connect_error) throw new Exception($CONN->connect_error);
$DBLIB = new MysqliDb ($CONN); //Re-use it in the lib we love

/* FUNCTIONS */
class bCMS {
    function sanitizeString($var) {
        //Setup Sanitize String Function
        $var = strip_tags($var);
        $var = htmlentities($var);
        $var = stripslashes($var);
        global $CONN;
        return mysqli_real_escape_string($CONN, $var);
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

        global $CONN;
        return mysqli_real_escape_string($CONN, $clean_html);
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
            "auditLog_targetID" => $this->sanitizeString($targetid)
        ];
        if ($userid > 0) $data["users_userid"] = $this->sanitizeString($userid);
        if ($useridTo > 0) $data["auditLog_actionUserid"] = $this->sanitizeString($useridTo);

        if ($DBLIB->insert("auditLog", $data)) return true;
        else return false;
    }
    function s3List($typeid, $subTypeid = false, $sort = 's3files_meta_uploaded', $sortOrder = 'ASC') {
        global $DBLIB, $CONFIG;
        $DBLIB->where("s3files_meta_type", $typeid);
        if ($subTypeid) $DBLIB->where("s3files_meta_subType", $subTypeid);
        $DBLIB->where("(s3files_meta_deleteOn >= '". date("Y-m-d H:i:s") . "' OR s3files_meta_deleteOn IS NULL)"); //If the file is to be deleted soon or has been deleted don't let them download it
        $DBLIB->where("s3files_meta_physicallyStored",1); //If we've lost the file or deleted it we can't actually let them download it
        $DBLIB->orderBy($sort, $sortOrder);
        return $DBLIB->get("s3files", null, ["s3files_id", "s3files_extension", "s3files_name","s3files_meta_size", "s3files_meta_uploaded"]);
    }
    function s3URL($fileid, $size = false, $forceDownload = false, $expire = '+10 minutes') {
        global $DBLIB, $CONFIG;
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
        $s3Client = new Aws\S3\S3Client([
            'region'  => $file["s3files_region"],
            'endpoint' => "https://" . $file["s3files_endpoint"],
            'version' => 'latest',
            'credentials' => array(
                'key'    => $CONFIG['AWS']['KEY'],
                'secret' => $CONFIG['AWS']['SECRET'],
            )
        ]);

        $file['expiry'] = $expire;


        switch ($file['s3files_meta_type']) {
            case 1:
                //This is a user thumbnail
                break;
            case 2:
                // Asset type thumbnail
            case 3:
                // Asset type file
            case 4:
                // Asset file
            case 5:
                // Instance thumbnail
            case 6:
                // Instance file
            case 7:
                //Project file
            case 8:
                // Maintenance job file
            default:
                //There are no specific requirements for this file so not to worry.
        }

        $parameters = [
            'Bucket' => $file['s3files_bucket'],
            'Key'    => $file['s3files_path'] . "/" . $file['s3files_filename'] . '.' . $file['s3files_extension'],
        ];
        if ($forceDownload) $parameters['ResponseContentDisposition'] = 'attachment; filename="' . $CONFIG['PROJECT_NAME'] . ' ' . $file['s3files_filename'] . '.' . $file['s3files_extension'] . '"';
        $cmd = $s3Client->getCommand('GetObject', $parameters);
        $request = $s3Client->createPresignedRequest($cmd, $file['expiry']);
        $presignedUrl = (string) $request->getUri();

        //$presignedUrl = $file['s3files_cdn_endpoint'] . explode($file["s3files_endpoint"],$presignedUrl)[1]; //Remove the endpoint itself from the url in order to set a new one

        return $presignedUrl;
    }
}

$GLOBALS['bCMS'] = new bCMS;


