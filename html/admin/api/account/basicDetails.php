<?php
require_once __DIR__ . '/../apiHeadSecure.php';

header('Content-Type:text/plain');

if (isset($_POST['username'])) {
    if ($_POST['userid'] == "NEW" and $AUTH->permissionCheck(4)) $newUser = true; //Are we making a new user here?
    else $newUser = false;

    if ($AUTH->permissionCheck(5) && $USERDATA['users_userid'] != $_POST['userid'] && !$newUser) {
        $DBLIB->where("users_userid", $bCMS->sanitizeString($_POST['userid']));
        $thisUser = $DBLIB->getone("users", ["users_userid", "users_email", "users_username"]);
        if (!$thisUser) die("5");
        $userid = $thisUser["users_userid"];
    } else {
        $userid = $USERDATA['users_userid'];
        $thisUser = false;
    }


    if (
        (
            (!$newUser && !$thisUser && strtolower($_POST['email']) != $USERDATA['users_email']) //This users' user account
          or ($thisUser && strtolower($_POST['email']) != $thisUser['users_email']) //Existing user account being edited
            or $newUser
        ) && $AUTH->emailTaken($bCMS->sanitizeString(strtolower($_POST['email'])))) die("Email taken");
    elseif (
        (
            (!$newUser && !$thisUser && strtolower($_POST['username']) != $USERDATA['users_username']) //This users' user account
            or ($thisUser && strtolower($_POST['username']) != $thisUser['users_username']) //Existing user account being edited
            or $newUser
        ) && $AUTH->usernameTaken($bCMS->sanitizeString(strtolower($_POST['username'])))) die("Username taken");
    else {
        $data = Array (
            'users_email' => strtolower($bCMS->sanitizeString($_POST['email'])),
            'users_username' => strtolower($bCMS->sanitizeString($_POST['username'])),
            'users_name1' => $bCMS->sanitizeString($_POST['forename']),
            'users_name2' => $bCMS->sanitizeString($_POST['lastname'])
        );



        if (!$newUser) {
            $DBLIB->where('users_userid', $userid);
            if ($DBLIB->update('users', $data)) {
                if (($thisUser && $thisUser['users_email'] != strtolower($_POST['email'])) or (!$thisUser && strtolower($_POST['email']) != $USERDATA['users_email'])) {
                    //The email address has been changed
                    $DBLIB->where ('users_userid', $userid);
                    $DBLIB->update ('users', ["users_emailVerified" => "0"]); //Set E-Mail to unverified
                    $AUTH->verifyEmail($userid);
                }
                $bCMS->auditLog("UPDATE", "users", json_encode($data), $AUTH->data['users_userid'],$userid);
                die('1');
            } else die("2");
        } else {
            $data["users_salty1"] = $bCMS->randomString(8);
            $data["users_salty2"] = $bCMS->randomString(8);
            $data["users_hash"] = $CONFIG['nextHash'];
            $data["users_password"] = "RESET";
            $newUser = $DBLIB->insert("users", $data);
            if (!$newUser) die("6");
            else {
                $bCMS->auditLog("INSERT", "users", json_encode($data), $AUTH->data['users_userid'],$newUser);
                die("" . json_encode(["result" => true, "newUserId" => $newUser]));
            }
        }

    }
} else die('3');
?>
