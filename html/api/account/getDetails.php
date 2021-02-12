<?php
require_once __DIR__ . '/../apiHeadSecure.php';
$return = [
    "users_username" => $AUTH->data["users_username"],
    "users_name1" => $AUTH->data[ "users_name1"],
    "users_name2" => $AUTH->data["users_name2"],
    "users_email" => $AUTH->data[ "users_email"],
    "users_created" => $AUTH->data["users_created"],
    "users_thumbnail" => $AUTH->data["users_thumbnail"],
    "users_changepass" => ($AUTH->data["users_changepass"] == 1 ? true : false),
    "permissions" => $AUTH->permissions
];
finish(true, null, $return);
?>
