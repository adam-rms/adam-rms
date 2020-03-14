<?php
require_once __DIR__ . '/../../config.php';

class bID
{
    public $login;
    private $token;
    public $data;
    private $permissions;
    function __construct()
    {
        global $DBLIB;
        if (isset($_SESSION['token'])) {
            //Time to check whether it is valid
            $DBLIB->where('authTokens_token', $GLOBALS['bCMS']->sanitizeString($_SESSION['token']));
            $DBLIB->where("authTokens_valid", '1');
            $tokencheckresult = $DBLIB->getOne("authTokens");
            if ($tokencheckresult != null) {
                if ((strtotime($tokencheckresult["authTokens_created"]) + 1 * 12 * (3600 * 1000)) < time() or $tokencheckresult["authTokens_ipAddress"] != (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER["REMOTE_ADDR"])) {
                    $this->login = false;
                } //Check token hasn't expired and check if the IP matches that preset in table
                else {
                    //Get user data
                    $DBLIB->where("users_userid", $tokencheckresult["users_userid"]);
                    $this->data = $DBLIB->getOne("users");
                    if ($this->data == null) $this->login = false;
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



                        $DBLIB->orderBy("instancePositions_rank", "ASC");
                        $DBLIB->orderBy("instancePositions_displayName", "ASC");
                        $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id", "LEFT");
                        $DBLIB->join("instances", "instancePositions.instances_id=instances.instances_id", "LEFT");
                        $DBLIB->where("users_userid", $this->data['users_userid']);
                        $DBLIB->where("userInstances_deleted", 0);
                        $DBLIB->where("instances.instances_deleted", 0);
                        $instances = $DBLIB->get("userInstances");
                        $this->data['instances'] = [];
                        $this->data['instance_ids'] = [];
                        foreach ($instances as $instance) {
                            if ($instance['instances_weekStartDates'] != null) {
                                $dates = explode("\n", $instance['instances_weekStartDates']);
                                $instance['weekStartDates'] = [];
                                foreach ($dates as $date) {
                                    array_push($instance['weekStartDates'], strtotime($date)*1000);
                                }
                                unset($dates);
                                sort($instance['weekStartDates']);
                            } else $instance['weekStartDates'] = false;

                            $instance['permissions'] = array_unique(array_merge(explode(",", $instance['instancePositions_actions']), explode(",", $instance['userInstances_extraPermissions'])));
                            $this->data['instances'][] = $instance;
                            array_push($this->data['instance_ids'], $instance['instances_id']);
                        }
                        $this->data['instance'] = false;
                        if ($this->data['users_selectedInstanceID'] != null) $this->setInstance($this->data['users_selectedInstanceID']);
                        if (!$this->data['instance'] and count($this->data['instances']) >0) {
                            $this->setInstance($this->data['instances'][0]['instances_id']);
                        }
                    }
                }
            } else $this->login = false;
        } else {
            $this->login = false;
        }
    }
    public function permissionCheck($permissionId) {
        if (!$this->login) return false; //Not logged in
        if (in_array($permissionId, $this->permissions)) return true;
        else return false;
    }

    public function instancePermissionCheck($permissionId) {
        if (!$this->login) return false; //Not logged in
        if (!$this->data['instance']) return false;
        if (in_array($permissionId, $this->data['instance']['permissions'])) return true;
        else return false;
    }
    public function setInstance($instanceId) {
        global $DBLIB;
        if (!$this->login) return false; //Not logged in
        foreach ($this->data['instances'] as $instance) {
            //Check they have this instance available
            if ($instance['instances_id'] == $instanceId) {
                $this->data['instance'] = $instance;
                if ($this->data['instance']['instances_id'] != $this->data["users_selectedInstanceID"]) {
                    //Change it in db
                    $DBLIB->where("users_userid", $this->data['users_userid']);
                    $DBLIB->update("users", ["users_selectedInstanceID" => $this->data['instance']['instances_id']]); //Allow users to keep other projects selected
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

    function generateToken($userid, $redirect = true, $adminuserid = null, $returntoken = false)
    {
        global $CONFIG, $DBLIB;

        $tokenalias = $this->generateTokenAlias();
        $data = Array("authTokens_created" => date('Y-m-d G:i:s'),
            "authTokens_token" => $tokenalias,
            "users_userid" => $userid,
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
        if (sendEmail($userid,  false,"Verify your E-Mail", '<h1 style="margin: 0 0 10px 0; font-family: sans-serif; font-size: 25px; line-height: 30px; color: #333333; font-weight: normal;">Verify your E-Mail address!</h1><p style="margin: 0;">Please <a href="' . $CONFIG['ROOTURL'] . '/api/account/verifyEmail.php?code=' . $code . '">verify your E-Mail address for ' . $CONFIG['PROJECT_NAME'] . '</a></p><br/><i><b>N.B.</b>The link in this E-Mail will only last for 48 hours!</i>')) return true;
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
        $data = Array("users_userid" => $userid,
            "passwordResetCodes_timestamp" => date('Y-m-d G:i:s'),
            "passwordResetCodes_code" => $code
        );
        if (!$DBLIB->insert('passwordResetCodes', $data)) throw new Exception('Fatal Error sending a reset E-Mail');
        if (sendEmail($userid,false,  "Reset your password", '<h1 style="margin: 0 0 10px 0; font-family: sans-serif; font-size: 25px; line-height: 30px; color: #333333; font-weight: normal;">Someone requested to reset your password - if this was not you please contact our support team urgently.</h1><p style="margin: 0;"><a href="' . $CONFIG['ROOTURL'] . '/api/account/passwordReset.php?code=' . $code . '">Reset account password for ' . $CONFIG['PROJECT_NAME'] . '</a></p><br/><i><b>N.B.</b>The link in this E-Mail will only last for 48 hours!</i>')) return true;
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
