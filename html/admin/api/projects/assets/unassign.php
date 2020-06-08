<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(31) or (!isset($_POST['assetsAssignments']) and !isset($_POST['assets_id']))) die("404");

if (isset($_POST['assets_id']) and !isset($_POST['assetsAssignments'])) {
    //Convert for where only the asset id and project is known
    $DBLIB->where("assets_id", $_POST['assets_id']);
    $DBLIB->where("projects_id", $AUTH->data['users_selectedProjectID']);
    $DBLIB->where("assetsAssignments_deleted", 0);
    $assignment = $DBLIB->getone("assetsAssignments", ["assetsAssignments_id"]);
    if ($assignment) $_POST['assetsAssignments'] = [$assignment['assetsAssignments_id']];
    else finish(false,["message"=>"Could not find assignment"]);
}

$assignmentsRemove = new assetAssignmentSelector($_POST['assetsAssignments']);
$assignmentsRemove = $assignmentsRemove->getData();
if (!$assignmentsRemove['projectid']) finish(false,["message"=>"Cannot find projectid"]);
$DBLIB->where("projects.projects_id", $assignmentsRemove["projectid"]);
$DBLIB->where("projects.instances_id IN (" . implode(",", $AUTH->data['instance_ids']) . ")");
$DBLIB->where("projects.projects_deleted", 0);
$project = $DBLIB->getone("projects",["projects_id","projects_dates_deliver_start","projects_dates_deliver_end"]);
if (!$project) finish(false,["message"=>"Cannot find project"]);

$projectFinanceHelper = new projectFinance();
$priceMaths = $projectFinanceHelper->durationMaths($project['projects_dates_deliver_start'],$project['projects_dates_deliver_end']);
$projectFinanceCacher = new projectFinanceCacher($project['projects_id']);

foreach ($assignmentsRemove["assignments"] as $assignment) {
    $DBLIB->where("assetsAssignments_id", $assignment['assetsAssignments_id']);
    if (!$DBLIB->update("assetsAssignments", ["assetsAssignments_deleted" => 1])) finish(false);
    else {
        $bCMS->auditLog("UNASSIGN-ASSET", "assetsAssignments", $assignment['assetsAssignments_id'], $AUTH->data['users_userid'],null, $assignment['projects_id']);
        $projectFinanceCacher->adjust('projectsFinanceCache_mass',-1*($assignment['assets_mass'] !== null ? $assignment['assets_mass'] : $assignment['assetTypes_mass']));
        $projectFinanceCacher->adjust('projectsFinanceCache_value',-1*($assignment['assets_value'] !== null ? $assignment['assets_value'] : $assignment['assetTypes_value']));

        if ($assignment['assetsAssignments_customPrice'] > 0) {
            $price = $assignment['assetsAssignments_customPrice'];
        } else {
            $priceChange = 0.0;
            $priceChange += ($priceMaths['days'] * ($assignment['assets_dayRate'] !== null ? $assignment['assets_dayRate'] : $assignment['assetTypes_dayRate']));
            $priceChange += ($priceMaths['weeks'] * ($assignment['assets_weekRate'] !== null ? $assignment['assets_weekRate'] : $assignment['assetTypes_weekRate']));
            $price = round($priceChange, 2, PHP_ROUND_HALF_UP);
        }
        $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', -1*$price);
        if ($assignment['assetsAssignments_discount'] > 0) $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', -1*($price-(round(($price * (1 - ($assignment['assetsAssignments_discount'] / 100))), 2, PHP_ROUND_HALF_UP))));
    }
}
if ($projectFinanceCacher->save()) finish(true);
else finish(false,["message"=>"Finance Cacher Save failed"]);