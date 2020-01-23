<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(3) or !isset($_POST['rolegroup'])) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

if (count($_POST['users']) < 1) finish(true);

foreach ($_POST['users'] as $user) {
    $DBLIB->where("instancePositions_id", $_POST['rolegroup']);
    $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
    $position = $DBLIB->getone("instancePositions", ["instancePositions_id"]);
    if (!$position) finish(false, ["code" => "ADD-USER-TO-INSTANCE-FAIL-NOPOSITION", "message"=> "Could not add user to Business"]);

    if (!$DBLIB->insert("userInstances", [
        "users_userid" => $user,
        "instancePositions_id" => $position["instancePositions_id"],
        "userInstances_label" => $_POST['rolename']
    ])) finish(false, ["code" => "ADD-USER-TO-INSTANCE-FAIL", "message"=> "Could not add user to Business"]);

    sendemail($user, $AUTH->data['users_name1'] . " " . $AUTH->data['users_name2'] . " added you to " . $AUTH->data['instance']['instances_name'],
        "<center><h2>Welcome to " . $AUTH->data['instance']['instances_name'] . '</h2><br/><p><a href="' . $CONFIG['ROOTURL'] . '/">Login to ' . $CONFIG['PROJECT_NAME'] . '</a> to start work!</p><br/><i>' . $AUTH->data['users_name1'] . " " . $AUTH->data['users_name2'] . " added you with role " . $bCMS->sanitizeString($_POST['rolename']) . '</i></center>'
        );
}
finish(true);