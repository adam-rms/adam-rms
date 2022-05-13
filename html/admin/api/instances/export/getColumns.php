<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(133)) die("404");

if (!isset($_POST['table_name'])) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

/**
 * READ ME!
 * There is a lot of hardcoded data in this file and this is for a reason!
 * Whilst it is very possible to just get columns from a database, there is a lot of 
 * information that either is AdamRMS specific, or should not be exportable from a system.
 */

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
        "asset_definableFields_1",
        "asset_definableFields_2",
        "asset_definableFields_3",
        "asset_definableFields_4",
        "asset_definableFields_5",
        "asset_definableFields_6",
        "asset_definableFields_7",
        "asset_definableFields_8",
        "asset_definableFields_9",
        "asset_definableFields_10"
    ],
    "projects" => [
        "projects_id",
        "projects_name",
        "projects_description",
        "projects_dates_use_start",
        "projects_dates_use_end",
        "projects_dates_deliver_start",
        "projects_dates_deliver_end",
        "projects_invoiceNotes",
    ]
];

finish(true, null, $columns[$_POST['table_name']]);
?>