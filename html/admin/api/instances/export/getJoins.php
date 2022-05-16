<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(133)) die("404");

if (!isset($_POST['table_name'])) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

//["name" => Screen name, "value" => table name]
$tables = [
    "assets" => [
        ["name" => "Asset Types", "value" => "assettypes"],
    ],
    "assettypes" => [
        ["name" => "Manufacturers", "value" => "manufacturers"],
        ["name" => "Asset Categories", "value" => "assetcategories"]
    ],
    "projects" => [
        ["name" => "Locations", "value" => "locations"],
        ["name" => "Clients", "value" => "clients"],
        ["name" => "Users", "value" => "users"],
    ]
];

finish(true, null, $tables[$_POST['table_name']]);
?>