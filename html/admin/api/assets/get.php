<?php
/**
 * API
 * \assets\get.php
 * Gets information about an asset, based on a given argument
 *
 * Arguments:
 *  - (*) assetsAssignments_id: an Asset Assignment ID
 *  - (*) assets_id: an Asset ID
 *  - (*) assetTypes_id: an Asset Type ID
 *        - projects_id: a Project ID to filter an Asset Type's assets to those available to that project.
 * One of (*) is required
 *
 * Returns:
 *      "result": true,
 *      "response": [] - array of assets
 *   -or-
 *      "result": true
 *      "error": ["code" => "NO-ASSETS", "message"=>"No Assets Available"] - no assets error message
 *  -or-
 *      "result": false
 *      "error": [] - error code
 */

require_once __DIR__ . '/../apiHeadSecure.php';

if (isset($_POST['assetsAssignments_id'])) {
    $DBLIB->join("assetsAssignments", "assetsAssignments.assets_id = assets.assets_id");
    $DBLIB->where("assetsAssignments.assetsAssignments_id", $_POST['assetsAssignments_id']);
    $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
} else if (isset($_POST['assets_id'])){
    $DBLIB->where("assets.assets_id", $_POST['assets_id']);
} else if (isset($_POST['assetTypes_id'])){
    if (isset($_POST['projects_id'])){
        //get dates of user's current project to filter by available assets
        $DBLIB->where("projects_id", $_POST['projects_id']);
        $DBLIB->where("projects.projects_deleted", 0);
        $DBLIB->where("projects_dates_deliver_start",NULL,"IS NOT");
        $DBLIB->where("projects_dates_deliver_end",NULL,"IS NOT");
        $thisProject = $DBLIB->getone("projects",["projects_dates_deliver_start","projects_dates_deliver_end"]);

        $validProjects = [$_POST['projects_id']];
        $DBLIB->where("projects_deleted", 0);
        $DBLIB->where("projects_archived", 0);
        $DBLIB->where("((projects_dates_deliver_start >= '" . date ("Y-m-d H:i:s",strtotime($thisProject['projects_dates_deliver_start']))  . "' AND projects_dates_deliver_start <= '" . date ("Y-m-d H:i:s",strtotime($thisProject['projects_dates_deliver_end'])) . "') OR (projects_dates_deliver_end >= '" . date ("Y-m-d H:i:s",strtotime($thisProject['projects_dates_deliver_start'])) . "' AND projects_dates_deliver_end <= '" . date ("Y-m-d H:i:s",strtotime($thisProject['projects_dates_deliver_end'])) . "') OR (projects_dates_deliver_end >= '" . date ("Y-m-d H:i:s",strtotime($thisProject['projects_dates_deliver_end'])) . "' AND projects_dates_deliver_start <= '" . date ("Y-m-d H:i:s",strtotime($thisProject['projects_dates_deliver_start'])) . "'))");
        foreach ($DBLIB->get("projects", null, ["projects_id"]) as $item){
            array_push($validProjects, $item["projects_id"]);
        }

        $DBLIB->where("projects_id", $validProjects, "IN");
        $DBLIB->where("assetsAssignments_deleted", 0);
        $assignedAssets =[];
        foreach ($DBLIB->get("assetsAssignments", null, ["assets_id"]) as $item){
            array_push($assignedAssets, $item['assets_id']);
        }
        $DBLIB->where('assets_id', $assignedAssets, 'NOT IN');
    }
    $DBLIB->where('assetTypes_id', $_POST['assetTypes_id']);
    $DBLIB->where('assets_deleted', 0);
    $DBLIB->where('assets_archived', null, "IS");
}else {
    finish(false, ["code" => "NO-ID", "message"=> "Provide an Asset ID, Asset Type ID or an Asset Assignment ID"]); //No Id provided so is an error
}

$result = $DBLIB->get("assets");

if ($result) finish(true, null, $result);
finish(true, ["code" => "NO-ASSETS", "message"=>"No Assets Available"]); //this is not an error, just information that there are no assets!