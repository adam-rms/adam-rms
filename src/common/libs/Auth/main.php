<?php
require_once __DIR__ . '/instanceActions.php';
require_once __DIR__ . '/serverActions.php';
require_once __DIR__ . '/../Telemetry/Telemetry.php';
date_default_timezone_set($CONFIG['TIMEZONE']);
use \Firebase\JWT\JWT;

class AuthFail extends Exception {
    public function message() {
        //error message
        $errorMsg = 'Error on line '.$this->getLine().' in '.$this->getFile()
        .': <b>'.$this->getMessage().'</b><br/>';
        return $errorMsg;
    }
}
class bID
{
    const TOKEN_LENGTH = 32;
    public $VALIDMAGICLINKREDIRECTS = ["com.bstudios.adamrms://magic-link"];
    public $login;
    private $token;
    private $telemetry;
    public $data;
    public $debug = '';
    private $serverPermissions;
    private function getToken() {
        global $CONFIG;
        /**
         * Try to find a token to process
         */
        if (isset($_POST['jwt'])) {
            //JWTs come via POST from the mobile app
            try {
                $decoded = JWT::decode($_POST['jwt'], $CONFIG['AUTH_JWTKey'], array('HS256'));
                $decoded_array = (array) $decoded;
                if (!in_array($decoded_array['type'], ["app-v1", "app-v2-magic-email"])) throw new AuthFail('JWT type invalid for receipt via POST');
                return ["token" => $decoded_array['token'], "type" => $decoded_array['type']];
            } catch (\Firebase\JWT\ExpiredException $e) {
                throw new AuthFail('JWT expired');
            }
        } elseif (isset($_SESSION['token'])) return ["token" => $_SESSION['token'], "type" => "web-session"];
        else throw new AuthFail('No token found');
    }
    private function checkToken($tokenPayload) {
        global $DBLIB, $CONFIG;
        $token = $tokenPayload['token'];
        $tokenType = $tokenPayload['type'];

        if (!$token or strlen($token) < self::TOKEN_LENGTH) throw new AuthFail('Token invalid');

        $DBLIB->where('authTokens_token', $GLOBALS['bCMS']->sanitizeString($token));
        $DBLIB->where("authTokens_valid", '1');
        $DBLIB->where("authTokens_type", $tokenType);
        $tokenCheck = $DBLIB->getOne("authTokens", ["authTokens_token", "authTokens_created", "authTokens_ipAddress", "users_userid", "authTokens_adminId", "authTokens_type", "authTokens_id"]);

        if (!$tokenCheck) {
            throw new AuthFail('Token not found in DB');
        } elseif ((strtotime($tokenCheck["authTokens_created"]) + (12 * 60 * 60)) < time()) {
             // Tokens are valid for 12 hrs (this includes the mobile app), which matches the session timeout
            throw new AuthFail("Token expired at " . $tokenCheck["authTokens_created"] . " - server time is " . time());
        } elseif (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            if ($_SERVER["HTTP_CF_CONNECTING_IP"] != $tokenCheck["authTokens_ipAddress"]) {
                throw new AuthFail("IP from Cloudflare doesn't match token. Received [" . $_SERVER["HTTP_CF_CONNECTING_IP"] . "] but expecting [" . $tokenCheck["authTokens_ipAddress"] . "]");
            }
        } elseif(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            if (array_shift(explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"])) != $tokenCheck["authTokens_ipAddress"]) {
                throw new AuthFail("IP from Heroku/generic proxy doesn't match token. Received [" . array_shift(explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"])) . "] but expecting [" . $tokenCheck["authTokens_ipAddress"] . "]");
            }
        } elseif($_SERVER["REMOTE_ADDR"] != $tokenCheck["authTokens_ipAddress"]) {
            throw new AuthFail("IP direct doesn't match token. Received [" . $_SERVER["REMOTE_ADDR"] . "] but expecting [" . $tokenCheck["authTokens_ipAddress"] . "]");
        }

        //Tests have passed, return the token
        return $tokenCheck;
    }
    function __construct()
    {
        global $DBLIB, $CONFIG, $instanceActions, $serverActions;
        $this->telemetry = new Telemetry();
        try {
            //Get the token
            $this->token = $this->checkToken($this->getToken());

            //Download the user
            $DBLIB->where("users_userid", $this->token["users_userid"]);
            $DBLIB->where("users_deleted", 0);
            $this->data = $DBLIB->getOne("users");
            if (!$this->data) throw new AuthFail('User not found');

            //Admin "view site as" functionality
            $this->data['viewSiteAs'] = false;
            if ($this->token["authTokens_adminId"] != null) { 
                $DBLIB->where("users_userid", $this->token["authTokens_adminId"]);
                $this->data['viewSiteAs'] = $DBLIB->getOne("users");
            }

            $this->data['authTokens_id'] = $this->token["authTokens_id"];

            $this->login = true;
        } catch (AuthFail $e) {
            if ($CONFIG['DEV']) $this->debug = $e->getMessage();
            $this->login = false;
        }


        // Get the users server permissions
        $DBLIB->orderBy("positions_rank", "ASC");
        $DBLIB->orderBy("positions_displayName", "ASC");
        $DBLIB->join("positions", "userPositions.positions_id=positions.positions_id", "LEFT");
        $DBLIB->where("users_userid", $this->data['users_userid']);
        $positions = $DBLIB->get("userPositions");
        $this->data['positions'] = [];
        $permissionCodes = [];
        foreach ($positions as $position) {
            $this->data['positions'][] = $position;
            $position['groups'] = explode(",", $position['positions_positionsGroups']);
            foreach ($position['groups'] as $positiongroup) {
                $DBLIB->where("positionsGroups_id", $positiongroup);
                $positiongroup = $DBLIB->getone("positionsGroups", ["positionsGroups_actions"]);
                $permissionCodes = array_merge($permissionCodes, explode(",", $positiongroup['positionsGroups_actions']), explode(",", $position['userPositions_extraPermissions']));
            }
        }

        $this->serverPermissions = [];
        foreach ($permissionCodes as $permission) {
            if (in_array($permission, array_keys($serverActions)) and in_array($this->token['authTokens_type'], $serverActions[$permission]['Supported Token Types'])) {
                array_push($this->serverPermissions, $permission);
            }
        }
        $this->serverPermissions = array_unique($this->serverPermissions);

        // Get the users instance permissions
        $DBLIB->orderBy("instances.instances_id", "ASC");
        $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id", "LEFT");
        $DBLIB->join("instances", "instancePositions.instances_id=instances.instances_id", "LEFT");
        $DBLIB->where("users_userid", $this->data['users_userid']);
        $DBLIB->where("userInstances_deleted", 0);
        $DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
        $DBLIB->where("instances.instances_deleted", 0);
        $instances = $DBLIB->get("userInstances", null, ["instancePositions.*", "instances.*", "userInstances.*"]);
        $this->data['instances'] = [];
        $this->data['instance_ids'] = [];
        foreach ($instances as $instance) {
            $permissionsArray = [];
            //Seems to work better when done like this for the mobile app calls as it prevents the array becoming associative
            if ($instance['instancePositions_actions']) {
                $actionsArray = explode(",", $instance['instancePositions_actions']);
                foreach ($actionsArray as $action) {
                    array_push($permissionsArray,trim($action));
                }
            }
            if ($instance['userInstances_extraPermissions']) {
                $extraActionsArray = explode(",", $instance['userInstances_extraPermissions']);
                foreach ($extraActionsArray as $action) {
                    array_push($permissionsArray,trim($action));
                }
            }
            $permissionsArray = array_unique($permissionsArray);
            $instance['permissions'] = [];
            foreach ($permissionsArray as $permission) {
                if (in_array($permission, array_keys($instanceActions)) and in_array($this->token['authTokens_type'], $instanceActions[$permission]['Supported Token Types'])) {
                    array_push($instance['permissions'], $permission);
                }
            }

            $instance['publicData'] = json_decode($instance['instances_publicConfig'],true);
            $instance['calendarSettings'] = json_decode($instance['instances_calendarConfig'], true);
            $this->data['instances'][] = $instance;
            array_push($this->data['instance_ids'], $instance['instances_id']);
        }
        
        $this->data['instance'] = false;
        if (isset($_POST['instances_id'])) { //Used by the app
            $this->setInstance($_POST['instances_id']);
        } elseif (isset($_SESSION['instanceID']) and $_SESSION['instanceID'] != null and is_int($_SESSION['instanceID'])) {
            // An instance ID is set in their session so use that one
            if (in_array("INSTANCES:FULL_PERMISSIONS_IN_INSTANCE", $this->serverPermissions) and !in_array($_SESSION['instanceID'], $this->data['instance_ids']) and $this->token['authTokens_type'] == "web-session") {
                //They're assigned to an instance they are not in, and are a super administrator - so we need to download that instance as well for them
                $DBLIB->where("instances.instances_deleted", 0);
                $DBLIB->where("instances_id", $_SESSION['instanceID']);
                $instance = $DBLIB->getone("instances");
                if (!$instance) $this->data['instance'] = false;
                else {
                    $instance['userInstances_label'] = 'Server Admin Login';
                    $instance['permissions'] = array_keys($instanceActions);
                    array_push($this->data['instance_ids'], $_SESSION['instanceID']);
                    $this->data['instance'] = $instance;
                    $this->data['instances'][] = $instance;
                }
            } else $this->setInstance($_SESSION['instanceID']); //Set instance normally
        }
        if (!$this->data['instance'] and count($this->data['instances']) >0) {
            foreach ($this->data['instances'] as $instance) {
                if ($instance['instances_id'] == $this->data['users_selectedInstanceIDLast']) $this->setInstance($instance['instances_id']); //Try and pick the instance they last selected to make life nicer for them
            }
            if (!$this->data['instance']) $this->setInstance($this->data['instances'][0]['instances_id']); //No instance has been found - pick the first
        }
    }

    

    public function serverPermissionCheck($permissionKey) {
        if (!$this->login) return false; //Not logged in
        if (in_array($permissionKey, $this->serverPermissions)) return true;
        else return false;
    }

    public function instancePermissionCheck($permissionKey) {
        if (!$this->login) return false; //Not logged in
        if (!$this->data['instance'] or $this->data['instance']['permissions'] == null) return false;
        if (in_array($permissionKey, $this->data['instance']['permissions'])) return true;
        else return false;
    }
    public function setInstance($instanceId) {
        global $DBLIB;
        if (!$this->login) return false; //Not logged in
        foreach ($this->data['instances'] as $instance) {
            //Check they have this instance available
            if ($instance['instances_id'] == $instanceId) {
                $this->data['instance'] = $instance;
                if (!isset($_SESSION['instanceID']) or $this->data['instance']['instances_id'] != $_SESSION['instanceID'] or $this->data['instance']['instances_id'] != $this->data['users_selectedInstanceIDLast']) {
                    $_SESSION['instanceID'] = $this->data['instance']['instances_id'];
                    $DBLIB->where("users_userid",$this->data['users_userid']);
                    $DBLIB->update("users",["users_selectedInstanceIDLast"=>$this->data['instance']['instances_id']],1);
                }
                return true;
            }
        }
        return false;
    }
    private function generateTokenKey()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < self::TOKEN_LENGTH; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return md5(time() . $randomString);
    }

    function generateToken($userID, $adminUserID = false, $deviceType, $tokenType) {
        global $DBLIB;
        if (!in_array($tokenType, ["web-session", "app-v1", "app-v2-magic-email"])) throw new Exception("Unknown token type");
        if (is_null($userID)) throw new Exception("User ID cannot be null");

        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) $ipAddress = $_SERVER["HTTP_CF_CONNECTING_IP"];
        elseif(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) $ipAddress = array_shift(explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]));
        else $ipAddress = $_SERVER["REMOTE_ADDR"];

        $tokenKey = $this->generateTokenKey();
        $data = [
            "authTokens_created" => date('Y-m-d G:i:s'),
            "authTokens_token" => $tokenKey,
            "users_userid" => $userID,
            "authTokens_deviceType" => $deviceType,
            "authTokens_ipAddress" => $ipAddress,
            "authTokens_type" => $tokenType
        ];
        if ($adminUserID) $data["authTokens_adminId"] = intval($adminUserID); //Admin login as
        $token = $DBLIB->insert('authTokens', $data);

        if (!$token) throw new Exception("Cannot insert a newly created token into DB");

        $this->telemetry->logTelemetry();

        if ($tokenType == "web-session") $_SESSION['token'] = $tokenKey;

        return $tokenKey;
    }

    function redirectToReturnAddress() {
        global $CONFIG;
        //If the function call has asked for a redirect
        try {
            header('Location: ' . (isset($_SESSION['return']) ? $_SESSION['return'] :  $CONFIG['ROOTURL'])); //Check for session url to redirect to
        } catch (Exception $e) {
            die('<meta http-equiv="refresh" content="0;url=' . (isset($_SESSION['return']) ? $_SESSION['return'] : $CONFIG['ROOTURL']) . '" />');
        }
    }

    function issueJWT($token, $userID, $type)
    {
        global $CONFIG;
        $jwt = JWT::encode(array(
            "iss" => $CONFIG['ROOTURL'],
            "uid" => $userID,
            "token" => $token,
            "exp" => time()+12*60*60, //12 hours token expiry
            "iat" => time(),
            "type" => $type
        ), $CONFIG['AUTH_JWTKey']);
        return $jwt;
    }

    function logout()
    {
        global $DBLIB;
        if (isset($_SESSION['token'])) {
            $DBLIB->where("authTokens_token", $_SESSION['token']);
            $DBLIB->update('authTokens', ["authTokens_valid" => 0]);
        }
        $_SESSION = array();
        $this->login = false;
    }

    public function emailTaken($email)
    {
        global $DBLIB;
        if (strlen($email) < 1) return false;
        $email = trim(strtolower($email));
        $DBLIB->where("users_email", $email);
        if ($DBLIB->getValue("users", "count(*)") > 0) return true;
        else return false;
    }

    public function usernameTaken($username)
    {
        global $DBLIB;
        if (strlen($username) < 1) return false;
        $username = trim(strtolower($username)); //Usernames must be unique
        $DBLIB->where("users_username", $username);
        if ($DBLIB->getValue("users", "count(*)") > 0) return true;
        else return false;
    }

    function verifyEmail($userid = null)
    { //Verify a user's E-Mail address
        global $DBLIB, $CONFIG;

        if ($userid == null) $userid = $this->data['users_userid'];
        else $userid = $GLOBALS['bCMS']->sanitizeString($userid);

        $DBLIB->where("users_userid", $userid);
        $DBLIB->where("users_emailVerified", 0);
        $DBLIB->where("users_email IS NOT NULL");
        if ($DBLIB->getValue("users", "count(*)") != 1) return false;

        $DBLIB->where('users_userid', $userid);
        $DBLIB->update('emailVerificationCodes', ["emailVerificationCodes_valid" => "0"]); //Set all the previous codes to invalid
        $code = md5($GLOBALS['bCMS']->randomString(100) . $userid . time()) . time();
        $data = Array("users_userid" => $userid,
            "emailVerificationCodes_timestamp" => date('Y-m-d G:i:s'),
            "emailVerificationCodes_code" => $code
        );
        if (!$DBLIB->insert('emailVerificationCodes', $data)) throw new Exception('Fatal Error verifiying E-Mail');
        require_once __DIR__ . '/../../../api/notifications/main.php';
        if (notify(3, $userid,  false,"Verify your E-Mail", '<h1 style="margin: 0 0 10px 0; font-family: sans-serif; font-size: 25px; line-height: 30px; color: #333333; font-weight: normal;">Verify your E-Mail address!</h1><p style="margin: 0;">Please <a href="' . $CONFIG['ROOTURL'] . '/api/account/verifyEmail.php?code=' . $code . '">verify your E-Mail address for ' . $CONFIG['PROJECT_NAME'] . '</a></p><br/><i><b>N.B.</b>The link in this E-Mail will only last for 48 hours!</i>')) return true;
        else return false;
    }

    function forgotPassword($userid = null)
    { //Sent out a password reset email
        global $DBLIB, $CONFIG;

        if ($userid == null) $userid = $this->data['users_userid'];
        else $userid = $GLOBALS['bCMS']->sanitizeString($userid);

        $DBLIB->where('users_userid', $userid);
        $DBLIB->update('passwordResetCodes', ["passwordResetCodes_valid" => "0"]); //Set all the previous codes to invalid
        $code = md5($GLOBALS['bCMS']->randomString(100) . $userid . time()) . time();
        $data = Array(
            "users_userid" => $userid,
            "passwordResetCodes_timestamp" => date('Y-m-d G:i:s'),
            "passwordResetCodes_code" => $code
        );
        if (!$DBLIB->insert('passwordResetCodes', $data)) throw new Exception('Fatal Error sending a reset E-Mail');
        require_once __DIR__ . '/../../../api/notifications/main.php';
        if (notify(1,$userid,false,  "Reset your password", '<h1 style="margin: 0 0 10px 0; font-family: sans-serif; font-size: 25px; line-height: 30px; color: #333333; font-weight: normal;">Someone requested to reset your password</h1><p style="margin: 0;">If this was not you, please contact our support team urgently.<br /><br /><a href="' . $CONFIG['ROOTURL'] . '/api/account/passwordReset.php?code=' . $code . '">Reset account password for ' . $CONFIG['PROJECT_NAME'] . '</a></p><br/><i><b>N.B.</b>The link in this E-Mail will only last for 48 hours!</i>')) return true;
        else return false;
    }
    function destroyTokens($userid = null) {
        global $DBLIB, $CONFIG;

        if ($userid == null) $userid = $this->data['users_userid'];
        else $userid = $GLOBALS['bCMS']->sanitizeString($userid);

        $DBLIB->where ('users_userid', $userid);
        if ($DBLIB->update ('authTokens', ["authTokens_valid" => 0])) return true;
        else return false;
    }

    function sendMagicLink($email, $redirect) {
        global $DBLIB, $CONFIG;

        if (strlen($email) < 1) return false;
        $email = trim(strtolower($email));
        $DBLIB->where("users_email", $email);
        $DBLIB->where("users_emailVerified", 1);
        $DBLIB->where("users_email IS NOT NULL");
        $userID = $DBLIB->getValue("users", "users_userid");
        if (!$userID) return false;

        if (!in_array($redirect, $this->VALIDMAGICLINKREDIRECTS) and !$CONFIG['DEV']) return false; //Only allow certain redirects in production, as otherwise this is a vector to spam users with any email text you like

        $token = $this->generateToken($userID, false, "App v2", "app-v2-magic-email");
        $jwt = $this->issueJWT($token, $userID, "app-v2-magic-email");
        if (!$token or !$jwt) return false;
        require_once __DIR__ . '/../../../api/notifications/main.php';
        if (notify(4, $userID,  false,"Login to AdamRMS App", '<h1 style="margin: 0 0 10px 0; font-family: sans-serif; font-size: 25px; line-height: 30px; color: #333333; font-weight: normal;">Login to the app</h1><p style="margin: 0;"><a href="' . $CONFIG['ROOTURL'] . "/login/?app-magiclink=" . $redirect . "&magic-token=" . $jwt . '">Click to login to the mobile app</a></p><br/><i><b>N.B.</b>If you did not request this code, please do not click the link, and contact our support team</i>')) return true;
        else return false;
    }
}
?>
