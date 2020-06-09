<?php
header('Content-type: application/json');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");

//Copy the payload over to get&post to maintain compatibility between the app and the frontend
$dataPayload = json_decode(file_get_contents('php://input'));
$dataPayload = (array) $dataPayload;
foreach ($dataPayload as $key=>$item) {
    if (is_array($item) or is_object($item)) continue; //Do this for simple values only for now
    $_GET[$key] = $item;
    $_POST[$key] = $item;
}

require_once __DIR__ . '/../common/head.php';
//To prevent errors showing on the json output
error_reporting(0);
ini_set('display_errors', 0);

//Finish function
function finish($result = false, $error = ["code" => null, "message"=> null], $response = []) {
    $dataReturn = ["result" => $result];
    if ($error) $dataReturn["error"] = $error;
    else $dataReturn["response"] = $response;

    die(json_encode($dataReturn));
}

class assetAssignmentSelector
{
    private $assignmentsProcess;
    private $projectid = false;
    private function selectAssignments($linkedassigmentid, $assignmentid = false)
    {
        global $DBLIB, $AUTH;
        if ($assignmentid) $DBLIB->where("assetsAssignments_id", $assignmentid);
        elseif ($linkedassigmentid) $DBLIB->where("assetsAssignments_linkedTo", $linkedassigmentid);
        else return false;
        $DBLIB->where("projects.instances_id IN (" . implode(",", $AUTH->data['instance_ids']) . ")");
        $DBLIB->where("projects.projects_deleted", 0);
        $DBLIB->where("assets_deleted", 0);
        $DBLIB->where("assetsAssignments_deleted", 0);
        $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
        $DBLIB->join("assets", "assetsAssignments.assets_id=assets.assets_id","LEFT");
        $DBLIB->join("assetTypes","assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
        $assignments = $DBLIB->get("assetsAssignments", null, ["assetsAssignments_id", "assetsAssignments.projects_id","assetsAssignments.assetsAssignments_discount","assetsAssignments.assetsAssignments_customPrice","projects.projects_id","assetsAssignments.assets_id","assets.assets_dayRate","assets.assets_weekRate","assetTypes_dayRate","assetTypes_weekRate","assetTypes_mass","assetTypes_value","assets.assets_value","assets.assets_mass"]);
        if (!$assignments) return false;
        foreach ($assignments as $assignment) {
            $this->projectid = $assignment['projects_id'];
            $this->assignmentsProcess[] = $assignment;
            $this->selectAssignments($assignment['assetsAssignments_id']);
        }
    }
    public function __construct($assetsAssignments)
    {
        $this->assignmentsProcess = [];
        foreach ($assetsAssignments as $assignment) {
            $this->selectAssignments(false, $assignment);
        }
        if (count($this->assignmentsProcess) < 1) finish(false, ["message" => "No assignments to modify found"]);
        else return true;
    }
    public function getData() {
        return ["assignments" => $this->assignmentsProcess, "projectid" => $this->projectid];
    }
}