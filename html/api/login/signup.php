<?php
require_once 'loginAjaxHead.php';

if (isset($_POST['name1']) and isset($_POST['password']) and isset($_POST['username']) and isset($_POST['email']) and isset($_POST['name2'])) {
    $data = Array (
        'users_email' => strtolower($bCMS->sanitizeString($_POST['email'])),
        'users_username' => strtolower($bCMS->sanitizeString($_POST['username'])),
        'users_name1' => $bCMS->sanitizeString($_POST['name1']),
        'users_name2' => $bCMS->sanitizeString($_POST['name2']),
        "users_salty1" => $bCMS->randomString(8),
        "users_salty2" => $bCMS->randomString(8),
        "users_hash" => $CONFIG['nextHash'],
    );
    $data["users_password"] = hash($data['users_hash'], $data['users_salty1'] . $_POST['password'] . $data['users_salty2']);
    $newUser = $DBLIB->insert("users", $data);
    if (!$newUser) finish(false, ["code" => null, "message" => "Can't create user"]);
    else {
        $bCMS->auditLog("INSERT", "users", json_encode($data), null,$newUser);
        $AUTH->verifyEmail($newUser);

        finish(true);
    }
} else finish(false, ["code" => null, "message" => "Unknown error"]);
?>
