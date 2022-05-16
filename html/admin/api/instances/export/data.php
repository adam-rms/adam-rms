<?php

//This file is the location of all the export related data. 
//It should not be called directly so, check if we're being included
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    die("404");
}

/**
 * READ ME!
 * There is a lot of hardcoded data in this file and this is for a reason!
 * Whilst it is very possible to just get columns from a database, there is a lot of 
 * information that either is AdamRMS specific, or should not be exportable from a system.
 * 
 * It also allows us to format the name a bit nicer and give a logical description for the column
 * which probs still need work to make sense for everyone!
 * 
 * This file has been centralised so that the final returned data can be checked before adding to the database!
 */

//The Columns for a given table that we want to be exportable
//columns: tablename => [name => Printable Name, value =>columnName, description => simple description for column ]
//This might be useful: ["name" => "", "value" => "", "description" => ""],
$columns = [
    "assets" => [
        ["name" => "Asset Tag", "value" => "assets.assets_tag",  "description" => "Unique Tag for this asset"],
        ["name" => "Notes", "value" => "assets.assets_notes",  "description" => "Asset Comments"],
        ["name" => "Day Rate", "value" => "assets.assets_dayRate",  "description" => "Hire charge for one day - overrides asset type"],
        ["name" => "Week Rate", "value" => "assets.assets_weekRate",  "description" => "Hire charge for one week - overrides asset type"],
        ["name" => "Value", "value" => "assets.assets_value",  "description" => "Purchase cost - overrides asset type"],
        ["name" => "Mass", "value" => "assets.assets_mass",  "description" => "Weight of asset  - overrides asset type"],
        ["name" => "Definable Fields 1", "value" => "assets.asset_definableFields_1", "description" => ""],
        ["name" => "Definable Fields 2", "value" => "assets.asset_definableFields_2", "description" => ""],
        ["name" => "Definable Fields 3", "value" => "assets.asset_definableFields_3", "description" => ""],
        ["name" => "Definable Fields 4", "value" => "assets.asset_definableFields_4", "description" => ""],
        ["name" => "Definable Fields 5", "value" => "assets.asset_definableFields_5", "description" => ""],
        ["name" => "Definable Fields 6", "value" => "assets.asset_definableFields_6", "description" => ""],
        ["name" => "Definable Fields 7", "value" => "assets.asset_definableFields_7", "description" => ""],
        ["name" => "Definable Fields 8", "value" => "assets.asset_definableFields_8", "description" => ""],
        ["name" => "Definable Fields 9", "value" => "assets.asset_definableFields_9", "description" => ""],
        ["name" => "Definable Fields 10", "value" => "assets.asset_definableFields_10", "description" => ""],
    ],
    "projects" => [
        ["name" => "ID", "value" => "projects.projects_id",  "description" => "Unique Tag for this asset"],
        ["name" => "Name", "value" => "projects.projects_name", "description" => ""],
        ["name" => "Description", "value" => "projects.projects_description", "description" => ""],
        ["name" => "Event Start Date", "value" => "projects.projects_dates_use_start", "description" => "Project event start date and time"],
        ["name" => "Event End Date", "value" => "projects.projects_dates_use_end", "description" => "Project event end date and time"],
        ["name" => "Event Dispatch Date", "value" => "projects.projects_dates_deliver_start", "description" => "Project asset dispatch date and time"],
        ["name" => "Event Return Date", "value" => "projects.projects_dates_deliver_end", "description" => "Project asset return date and time"],
        ["name" => "Invoice Notes", "value" => "projects.projects_invoiceNotes", "description" => "Notes for project invoice"],
    ],
    "assettypes" => [
        ["name" => "Name", "value" => "assettypes.assetTypes_name", "description" => ""],
        ["name" => "Description", "value" => "assettypes.assetTypes_description", "description" => ""],
        ["name" => "Product Link", "value" => "assettypes.assetTypes_productLink", "description" => ""],
        ["name" => "Definable Field Titles", "value" => "assettypes.assetTypes_definableFields", "description" => "Comma deliminated list of Definable field titles"],
        ["name" => "Mass", "value" => "assettypes.assetTypes_mass", "description" => "Weight of assetcost - overrides asset type"],
        ["name" => "Day Rate", "value" => "assettypes.assetTypes_dayRate", "description" => "Hire charge for one day - may be overridden by an individual asset"],
        ["name" => "Week Rate", "value" => "assettypes.assetTypes_weekRate", "description" => "Hire charge for one week - may be overridden by an individual asset"],
        ["name" => "Value", "value" => "assettypes.assetTypes_value", "description" => "Purchase cost - may be overridden by an individual asset"],
    ],
    "assetsbarcodes" => [
        ["name" => "Barcode", "value" => "assetsbarcodes.assetsBarcodes_value", "description" => "Barcode assigned to asset"],
        ["name" => "Barcode Type", "value" => "assetsbarcodes.assetsBarcodes_type", "description" => "Barcode type, usually 'CODE_128'"],
        ["name" => "Notes", "value" => "assetsbarcodes.assetsBarcodes_notes", "description" => ""],
    ],
    "assetsassignments" => [
        ["name" => "Assignment Comment", "value" => "assetsassignments.assetsAssignments_comment", "description" => ""],
        ["name" => "Custom Price", "value" => "assetsassignments.assetsAssignments_customPrice", "description" => "Asset's Custom price for this project"],
        ["name" => "Discount", "value" => "assetsassignments.assetsAssignments_discount", "description" => "Asset's Discount for this project"],
    ],
    "manufacturers" => [
        ["name" => "Name", "value" => "manufacturers.manufacturers_name", "description" => ""],
        ["name" => "Website", "value" => "manufacturers.manufacturers_website", "description" => ""],
        ["name" => "Notes", "value" => "manufacturers.manufacturers_notes", "description" => ""],
    ],
    "assetcategories" => [
        ["name" => "Name", "value" => "assetcategories.assetCategories_name", "description" => ""],
        ["name" => "Icon", "value" => "assetcategories.assetCategories_fontAwesome", "description" => "A FontAwesome Icon to represent the asset category"],
        ["name" => "Order", "value" => "assetcategories.assetCategories_rank", "description" => "Ordering of the category"],
    ],
    "assetcategoriesgroups" => [
        ["name" => "Name", "value" => "assetcategories.assetCategories_name", "description" => ""],
        ["name" => "Icon", "value" => "assetcategories.assetCategories_fontAwesome", "description" => "A FontAwesome Icon to represent the asset category group"],
        ["name" => "Order", "value" => "assetcategories.assetCategories_rank", "description" => "Ordering of the group"],
    ]
];

//Tables that can be joined with the given table
//"main table" => ["name" => Screen name, "value" => table name]
$joins = [
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

//Join query strings
//the strings required to join tables
//"main table" => ["tablename" => ["join"=> join string, "direction" => join direction]]
//this might help: "" => ["join" => "", "direction" => ""],
$joinQueries = [
    "assettypes" => [
        "manufacturers" => ["join" => "assettypes.manufacturers_id=manufacturers.manufacturers_id", "direction" => "LEFT"],
        "assetcategories" =>["join" => "assettypes.assetCategories_id=assetcategories.assetCategories_id", "direction" => "LEFT"],
    ],
    "assets" => [
        "assettypes" => ["join" => "assets.assetTypes_id=assettypes.assetTypes_id", "direction" => "LEFT"],
    ],
    "projects" => [
        "locations" => ["join" => "projects.locations_id=locations.locations_id", "direction" => "LEFT"],
        "clients" => ["join" => "projects.clients_id=clients.clients_id", "direction" => "LEFT"],
        "users" => ["join" => "projects.projects_manager=users.users_userid", "direction" => "LEFT"],
    ]
];