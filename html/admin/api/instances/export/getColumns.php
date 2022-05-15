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

//columns: tablename => [name => Printable Name, value =>columnName, description => simple description for column ]
$columns = [
    "assets" => [
        ["name" => "Asset Tag", "value" => "assets_tag",  "description" => "Unique Tag for this asset"],
        ["name" => "Notes", "value" => "assets_notes",  "description" => "Asset Comments"],
        ["name" => "Day Rate", "value" => "assets_dayRate",  "description" => "Hire charge for one day"],
        ["name" => "Week Rate", "value" => "assets_weekRate",  "description" => "Hire charge for one week"],
        ["name" => "Value", "value" => "assets_value",  "description" => "Purchase cost"],
        ["name" => "Mass", "value" => "assets_mass",  "description" => "Weight of asset"],
        ["name" => "Definable Fields 1", "value" => "asset_definableFields_1", "description" => ""],
        ["name" => "Definable Fields 2", "value" => "asset_definableFields_2", "description" => ""],
        ["name" => "Definable Fields 3", "value" => "asset_definableFields_3", "description" => ""],
        ["name" => "Definable Fields 4", "value" => "asset_definableFields_4", "description" => ""],
        ["name" => "Definable Fields 5", "value" => "asset_definableFields_5", "description" => ""],
        ["name" => "Definable Fields 6", "value" => "asset_definableFields_6", "description" => ""],
        ["name" => "Definable Fields 7", "value" => "asset_definableFields_7", "description" => ""],
        ["name" => "Definable Fields 8", "value" => "asset_definableFields_8", "description" => ""],
        ["name" => "Definable Fields 9", "value" => "asset_definableFields_9", "description" => ""],
        ["name" => "Definable Fields 10", "value" => "asset_definableFields_10", "description" => ""],
    ],
    "projects" => [
        ["name" => "ID", "value" => "projects_id",  "description" => "Unique Tag for this asset"],
        ["name" => "Name", "value" => "projects_name", "description" => "Project Name"],
        ["name" => "Description", "value" => "projects_description", "description" => ""],
        ["name" => "Event Start Date", "value" => "projects_dates_use_start", "description" => "Project event start date and time"],
        ["name" => "Event End Date", "value" => "projects_dates_use_end", "description" => "Project event end date and time"],
        ["name" => "Event Dispatch Date", "value" => "projects_dates_deliver_start", "description" => "Project asset dispatch date and time"],
        ["name" => "Event Return Date", "value" => "projects_dates_deliver_end", "description" => "Project asset return date and time"],
        ["name" => "Invoice Notes", "value" => "projects_invoiceNotes", "description" => "Notes for project invoice"],
    ]
];

finish(true, null, $columns[$_POST['table_name']]);
?>