<?php
require_once __DIR__ . '/../../config.php';
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
        global $DBLIB, $CONFIG;

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
                                asort($actionsArray);
                                foreach ($actionsArray as $action) {
                                    $instance['permissions'][]= (int)$action;
                                }
                            }
                            if ($instance['userInstances_extraPermissions']) {
                                $extraActionsArray = explode(",", $instance['userInstances_extraPermissions']);
                                asort($extraActionsArray);
                                foreach ($extraActionsArray as $action) {
                                    $instance['permissions'][]= (int)$action;
                                }
                            }
                            $this->data['instances'][] = $instance;
                            array_push($this->data['instance_ids'], $instance['instances_id']);
                        }
                        $this->data['instance'] = false;

                        if (isset($_POST['instances_id'])) { //Used by the app
                            $this->setInstance($_POST['instances_id']);
                        } elseif (isset($_SESSION['instanceID'])) {
                            if (in_array(21, $this->permissions) and $_SESSION['instanceID'] != null and !in_array($_SESSION['instanceID'], $this->data['instance_ids'])) {
                                //They're assigned to an instance they are not in - so we need to download that instance as well for them
                                $DBLIB->where("instances.instances_deleted", 0);
                                $DBLIB->where("instances_id", $_SESSION['instanceID']);
                                $instance = $DBLIB->getone("instances");
                                $instance['userInstances_label'] = 'BStudios Staff Login';
                                $instance['permissions'] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 124, 125, 126, 127, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140, 141, 142, 143, 144, 145, 146, 147, 148, 149, 150, 151, 152, 153, 154, 155, 156, 157, 158, 159, 160, 161, 162, 163, 164, 165, 166, 167, 168, 169, 170, 171, 172, 173, 174, 175, 176, 177, 178, 179, 180, 181, 182, 183, 184, 185, 186, 187, 188, 189, 190, 191, 192, 193, 194, 195, 196, 197, 198, 199, 200, 201, 202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 217, 218, 219, 220, 221, 222, 223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233, 234, 235, 236, 237, 238, 239, 240, 241, 242, 243, 244, 245, 246, 247, 248, 249, 250, 251, 252, 253, 254, 255, 256, 257, 258, 259, 260, 261, 262, 263, 264, 265, 266, 267, 268, 269, 270, 271, 272, 273, 274, 275, 276, 277, 278, 279, 280, 281, 282, 283, 284, 285, 286, 287, 288, 289, 290, 291, 292, 293, 294, 295, 296, 297, 298, 299, 300, 301, 302, 303, 304, 305, 306, 307, 308, 309, 310, 311, 312, 313, 314, 315, 316, 317, 318, 319, 320, 321, 322, 323, 324, 325, 326, 327, 328, 329, 330, 331, 332, 333, 334, 335, 336, 337, 338, 339, 340, 341, 342, 343, 344, 345, 346, 347, 348, 349, 350, 351, 352, 353, 354, 355, 356, 357, 358, 359, 360, 361, 362, 363, 364, 365, 366, 367, 368, 369, 370, 371, 372, 373, 374, 375, 376, 377, 378, 379, 380, 381, 382, 383, 384, 385, 386, 387, 388, 389, 390, 391, 392, 393, 394, 395, 396, 397, 398, 399, 400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417, 418, 419, 420, 421, 422, 423, 424, 425, 426, 427, 428, 429, 430, 431, 432, 433, 434, 435, 436, 437, 438, 439, 440, 441, 442, 443, 444, 445, 446, 447, 448, 449, 450, 451, 452, 453, 454, 455, 456, 457, 458, 459, 460, 461, 462, 463, 464, 465, 466, 467, 468, 469, 470, 471, 472, 473, 474, 475, 476, 477, 478, 479, 480, 481, 482, 483, 484, 485, 486, 487, 488, 489, 490, 491, 492, 493, 494, 495, 496, 497, 498, 499, 500, 501, 502, 503, 504, 505, 506, 507, 508, 509, 510, 511, 512, 513, 514, 515, 516, 517, 518, 519, 520, 521, 522, 523, 524, 525, 526, 527, 528, 529, 530, 531, 532, 533, 534, 535, 536, 537, 538, 539, 540, 541, 542, 543, 544, 545, 546, 547, 548, 549, 550, 551, 552, 553, 554, 555, 556, 557, 558, 559, 560, 561, 562, 563, 564, 565, 566, 567, 568, 569, 570, 571, 572, 573, 574, 575, 576, 577, 578, 579, 580, 581, 582, 583, 584, 585, 586, 587, 588, 589, 590, 591, 592, 593, 594, 595, 596, 597, 598, 599, 600, 601, 602, 603, 604, 605, 606, 607, 608, 609, 610, 611, 612, 613, 614, 615, 616, 617, 618, 619, 620, 621, 622, 623, 624, 625, 626, 627, 628, 629, 630, 631, 632, 633, 634, 635, 636, 637, 638, 639, 640, 641, 642, 643, 644, 645, 646, 647, 648, 649, 650, 651, 652, 653, 654, 655, 656, 657, 658, 659, 660, 661, 662, 663, 664, 665, 666, 667, 668, 669, 670, 671, 672, 673, 674, 675, 676, 677, 678, 679, 680, 681, 682, 683, 684, 685, 686, 687, 688, 689, 690, 691, 692, 693, 694, 695, 696, 697, 698, 699, 700, 701, 702, 703, 704, 705, 706, 707, 708, 709, 710, 711, 712, 713, 714, 715, 716, 717, 718, 719, 720, 721, 722, 723, 724, 725, 726, 727, 728, 729, 730, 731, 732, 733, 734, 735, 736, 737, 738, 739, 740, 741, 742, 743, 744, 745, 746, 747, 748, 749, 750, 751, 752, 753, 754, 755, 756, 757, 758, 759, 760, 761, 762, 763, 764, 765, 766, 767, 768, 769, 770, 771, 772, 773, 774, 775, 776, 777, 778, 779, 780, 781, 782, 783, 784, 785, 786, 787, 788, 789, 790, 791, 792, 793, 794, 795, 796, 797, 798, 799, 800, 801, 802, 803, 804, 805, 806, 807, 808, 809, 810, 811, 812, 813, 814, 815, 816, 817, 818, 819, 820, 821, 822, 823, 824, 825, 826, 827, 828, 829, 830, 831, 832, 833, 834, 835, 836, 837, 838, 839, 840, 841, 842, 843, 844, 845, 846, 847, 848, 849, 850, 851, 852, 853, 854, 855, 856, 857, 858, 859, 860, 861, 862, 863, 864, 865, 866, 867, 868, 869, 870, 871, 872, 873, 874, 875, 876, 877, 878, 879, 880, 881, 882, 883, 884, 885, 886, 887, 888, 889, 890, 891, 892, 893, 894, 895, 896, 897, 898, 899, 900, 901, 902, 903, 904, 905, 906, 907, 908, 909, 910, 911, 912, 913, 914, 915, 916, 917, 918, 919, 920, 921, 922, 923, 924, 925, 926, 927, 928, 929, 930, 931, 932, 933, 934, 935, 936, 937, 938, 939, 940, 941, 942, 943, 944, 945, 946, 947, 948, 949, 950, 951, 952, 953, 954, 955, 956, 957, 958, 959, 960, 961, 962, 963, 964, 965, 966, 967, 968, 969, 970, 971, 972, 973, 974, 975, 976, 977, 978, 979, 980, 981, 982, 983, 984, 985, 986, 987, 988, 989, 990, 991, 992, 993, 994, 995, 996, 997, 998, 999];
                                array_push($this->data['instance_ids'], $_SESSION['instanceID']);
                                $this->data['instance'] = $instance;
                                $this->data['instances'][] = $instance;
                            } elseif ($_SESSION['instanceID'] != null) $this->setInstance($_SESSION['instanceID']); //Set instance normally
                        }
                        if (!$this->data['instance'] and count($this->data['instances']) >0) {
                            //No instance has been found
                            $this->setInstance($this->data['instances'][0]['instances_id']);
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
    public function permissionCheck($permissionId) {
        if (!$this->login) return false; //Not logged in
        if (in_array($permissionId, $this->permissions)) return true;
        else return false;
    }

    public function instancePermissionCheck($permissionId) {
        if (!$this->login) return false; //Not logged in
        if (!$this->data['instance'] or $this->data['instance']['permissions'] == null) return false;
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
                if (!isset($_SESSION['instanceID']) or $this->data['instance']['instances_id'] != $_SESSION['instanceID']) $_SESSION['instanceID'] = $this->data['instance']['instances_id'];
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
        $data = Array("users_userid" => $userid,
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
