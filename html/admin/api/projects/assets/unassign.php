<?php
require_once __DIR__ . '/../../apiHeadSecure.php';
use Money\Currency;
use Money\Money;
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

$assignmentsIDs = [];
foreach ($assignmentsRemove["assignments"] as $assignment) {
    if (in_array($assignment['assetsAssignments_id'],$assignmentsIDs)) continue; //Prevents removing something twice
    array_push($assignmentsIDs,$assignment['assetsAssignments_id']);
    $DBLIB->where("assetsAssignments_id", $assignment['assetsAssignments_id']);
    if (!$DBLIB->update("assetsAssignments", ["assetsAssignments_deleted" => 1])) finish(false);
    else {
        $bCMS->auditLog("UNASSIGN-ASSET", "assetsAssignments", $assignment['assetsAssignments_id'], $AUTH->data['users_userid'],null, $assignment['projects_id']);
        $projectFinanceCacher->adjust('projectsFinanceCache_mass',($assignment['assets_mass'] !== null ? $assignment['assets_mass'] : $assignment['assetTypes_mass']),true);
        $projectFinanceCacher->adjust('projectsFinanceCache_value',new Money(($assignment['assets_value'] !== null ? $assignment['assets_value'] : $assignment['assetTypes_value']), new Currency($AUTH->data['instance']['instances_config_currency'])),true);


        if ($assignment['assetsAssignments_customPrice'] > 0) {
            $price = new Money($assignment['assetsAssignments_customPrice'], new Currency($AUTH->data['instance']['instances_config_currency']));
        } else {
            $price = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
            $price = $price->add((new Money(($assignment['assets_dayRate'] !== null ? $assignment['assets_dayRate'] : $assignment['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['days']));
            $price = $price->add((new Money(($assignment['assets_weekRate'] !== null ? $assignment['assets_weekRate'] : $assignment['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['weeks']));
        }
        $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $price,true);
        if ($assignment['assetsAssignments_discount'] > 0) $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $price->subtract($price->multiply(1 - ($assignment['assetsAssignments_discount'] / 100))),true);
    }
}
if ($projectFinanceCacher->save()) finish(true);
else finish(false,["message"=>"Finance Cacher Save failed"]);