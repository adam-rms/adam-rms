<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(133)) die("404");

if (!isset($_POST['table_name'])) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$permissions = [
    "assets" => 59,
    "projects" => 20
];

//columns: tablename => [columnName]
$columns = [
    "assets" => [
        "assets_id", 
        "assets_tag", 
        "assets_notes", 
        "assets_dayRate", 
        "assets_weekRate", 
        "assets_value", 
        "assets_mass", 
        "asset_definableFields"
    ],
    "projects" => [
        "projects_id",
        "projects_name",
        "projects_descrption",
        "projects_dates_use_start",
        "projects_dates_use_end",
        "projects_dates_deliver_start",
        "projects_dates_deliver_end",
        "projects_invoiceNotes",
    ]
];

/*if (!in_array($_POST['table_name'], $permissions) || !$AUTH->instancePermissionCheck($permissions[$_POST['table_name']])){
     finish(false, ["code" => "PERM-ERROR", "message"=> "No permission for action"]);
}*/

finish(true, null, $columns[$_POST['table_name']]);
?>