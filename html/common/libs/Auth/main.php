<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/instanceActions.php';
date_default_timezone_set("UTC");
use \Firebase\JWT\JWT;

class bID
{
    public $login;
    private $token;
    public $data;
    public $debug = '';
    private $permissions;
    function __construct()
    {
        global $DBLIB, $CONFIG, $instanceActions;

        if (isset($_POST['jwt'])) {
            //Prefer to process JWTs over sessions
            try {
                $decoded = JWT::decode($_POST['jwt'], $CONFIG['JWTKey'], array('HS256'));
                $decoded_array = (array) $decoded;
                $token = $decoded_array['token'];
            } catch (\Firebase\JWT\ExpiredException $e) {
                //Token has expired
                $token = false;
            }
        } elseif (isset($_SESSION['token'])) $token = $_SESSION['token'];
        else $token = false;

        if ($token and strlen($token) > 0) {
            //Time to check whether it is valid
            $DBLIB->where('authTokens_token', $GLOBALS['bCMS']->sanitizeString($token));
            $DBLIB->where("authTokens_valid", '1');
            $tokencheckresult = $DBLIB->getOne("authTokens");
            if ($tokencheckresult != null) {
                if ((strtotime($tokencheckresult["authTokens_created"]) + (1 * 12 * (3600 * 1000))) < time()) {
                    if ($CONFIG['DEV']) $this->debug .= "Token expired at " . $tokencheckresult["authTokens_created"] . " - server time is " . time() . "<br/>";
                    $this->login = false;
                } else {
                    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                        if ($_SERVER["HTTP_CF_CONNECTING_IP"] != $tokencheckresult["authTokens_ipAddress"]) {
                            if ($CONFIG['DEV']) $this->debug .= "IP from Cloudflare doesn't match token<br/>";
                            $this->login = false;
                        }
                    } elseif(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                        if ($_SERVER["HTTP_X_FORWARDED_FOR"] != $tokencheckresult["authTokens_ipAddress"]) {
                            //TODO evaluate this as a security risk
                            if ($CONFIG['DEV']) $this->debug .= "IP from Heroku/generic proxy doesn't match token<br/>";
                            $this->login = false;
                        }
                    } else {
                        if($_SERVER["REMOTE_ADDR"] != $tokencheckresult["authTokens_ipAddress"]) {
                            if ($CONFIG['DEV']) $this->debug .= "IP direct doesn't match token<br/>";
                            $this->login = false;
                        }
                    }
                    //Get user data
                    $DBLIB->where("users_userid", $tokencheckresult["users_userid"]);
                    $this->data = $DBLIB->getOne("users");
                    if ($this->data == null) {
                        if ($CONFIG['DEV']) $this->debug .= "User not found <br/>";
                        $this->login = false;
                    }
                    else {
                        $this->token = $tokencheckresult;

                        if ($tokencheckresult["authTokens_adminId"] != null) { //Admin "view site as" functionality
                            $DBLIB->where("users_userid", $tokencheckresult["authTokens_adminId"]);
                            $this->data['viewSiteAs'] = $DBLIB->getOne("users");
                        } else $this->data['viewSiteAs'] = false;

                        $this->login = true;

                        //$DBLIB->where("userPositions_end >= '" . date('Y-m-d H:i:s') . "'");
                        //$DBLIB->where("userPositions_start <= '" . date('Y-m-d H:i:s') . "'");
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
                        $this->permissions = array_unique($permissionCodes);

                        $DBLIB->orderBy("instances.instances_id", "ASC");
                        $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id", "LEFT");
                        $DBLIB->join("instances", "instancePositions.instances_id=instances.instances_id", "LEFT");
                        $DBLIB->where("users_userid", $this->data['users_userid']);
                        $DBLIB->where("userInstances_deleted", 0);
                        $DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
                        $DBLIB->where("instances.instances_deleted", 0);
                        $instances = $DBLIB->get("userInstances");
                        $this->data['instances'] = [];
                        $this->data['instance_ids'] = [];
                        foreach ($instances as $instance) {
                            $instance['permissions'] = [];
                            //Seems to work better when done like this for the API calls as it prevents the array becoming associative
                            if ($instance['instancePositions_actions']) {
                                $actionsArray = explode(",", $instance['instancePositions_actions']);
                                foreach ($actionsArray as $action) {
                                    $instance['permissions'][]= trim($action);
                                }
                            }
                            if ($instance['userInstances_extraPermissions']) {
                                $extraActionsArray = explode(",", $instance['userInstances_extraPermissions']);
                                foreach ($extraActionsArray as $action) {
                                    $instance['permissions'][]= trim($action);
                                }
                            }
                            $instance['permissions'] = array_unique($instance['permissions']);

                            $instance['publicData'] = json_decode($instance['instances_publicConfig'],true);
                            $this->data['instances'][] = $instance;
                            array_push($this->data['instance_ids'], $instance['instances_id']);
                        }
                        $this->data['instance'] = false;

                        if (isset($_POST['instances_id'])) { //Used by the app
                            $this->setInstance($_POST['instances_id']);
                        } elseif (isset($_SESSION['instanceID']) and $_SESSION['instanceID'] != null and is_int($_SESSION['instanceID'])) {
                            // An instance ID is set in their session so use that one
                            if (in_array("INSTANCES:FULL_PERMISSIONS_IN_INSTANCE", $this->permissions) and !in_array($_SESSION['instanceID'], $this->data['instance_ids'])) {
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
                }
            } else {
                if ($CONFIG['DEV']) $this->debug .= "Token not found in db<br/>";
                $this->login = false;
            }
        } else {
            if ($CONFIG['DEV']) $this->debug .= "No session token<br/>";
            $this->login = false;
        }
    }
    public function serverPermissionCheck($permissionKey) {
        if (!$this->login) return false; //Not logged in
        if (in_array($permissionKey, $this->permissions)) return true;
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
    private function generateTokenAlias()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 30; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return md5(time() . $randomString);
    }

    function generateToken($userid, $redirect = true, $adminuserid = null, $returntoken = false, $deviceType = 'Web')
    {
        global $CONFIG, $DBLIB;

        $tokenalias = $this->generateTokenAlias();
        $data = Array("authTokens_created" => date('Y-m-d G:i:s'),
            "authTokens_token" => $tokenalias,
            "users_userid" => $userid,
            "authTokens_deviceType" => $deviceType,
            "authTokens_ipAddress" => isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER["REMOTE_ADDR"],
        );
        if ($adminuserid != null) {
            $data["authTokens_adminId"] = $adminuserid; //Admin login as
        }
        $token = $DBLIB->insert('authTokens', $data);

        if (!$token) throw new Exception("Cannot insert a newly created token into DB");

        $_SESSION['token'] = $tokenalias;

        if ($redirect) {
            //If the function call has asked for a redirect
            try {
                header('Location: ' . (isset($_SESSION['return']) ? $_SESSION['return'] :  $CONFIG['ROOTURL'])); //Check for session url to redirect to
            } catch (Exception $e) {
                die('<meta http-equiv="refresh" content="0;url=' . (isset($_SESSION['return']) ? $_SESSION['return'] : $CONFIG['ROOTURL']) . '" />');
            }
        } else if ($returntoken) return $tokenalias;
        else return true;
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
        require_once __DIR__ . '/../../../admin/api/notifications/main.php';
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
        require_once __DIR__ . '/../../../admin/api/notifications/main.php';
        if (notify(1,$userid,false,  "Reset your password", '<h1 style="margin: 0 0 10px 0; font-family: sans-serif; font-size: 25px; line-height: 30px; color: #333333; font-weight: normal;">Someone requested to reset your password - if this was not you please contact our support team urgently.</h1><p style="margin: 0;"><a href="' . $CONFIG['ROOTURL'] . '/api/account/passwordReset.php?code=' . $code . '">Reset account password for ' . $CONFIG['PROJECT_NAME'] . '</a></p><br/><i><b>N.B.</b>The link in this E-Mail will only last for 48 hours!</i>')) return true;
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
}
?>
