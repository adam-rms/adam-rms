<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(3) or !isset($_POST['rolegroup'])) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

if (count($_POST['users']) < 1) finish(true);

foreach ($_POST['users'] as $user) {
    if (!$DBLIB->insert("userInstances", [
        "users_userid" => $user,
        "instancePositions_id" => $_POST['rolegroup'],
        "userInstances_label" => $_POST['rolename']
    ])) finish(false, ["code" => "ADD-USER-TO-INSTANCE-FAIL", "message"=> "Could not add user to Business"]);
}
finish(true);