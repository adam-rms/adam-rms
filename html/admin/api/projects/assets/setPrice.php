<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(43) or !isset($_POST['assetsAssignments'])) die("404");
$_POST['assetsAssignments_customPrice'] = round($_POST['assetsAssignments_customPrice'], 2, PHP_ROUND_HALF_UP);
$assignmentsSetDiscount = new assetAssignmentSelector($_POST['assetsAssignments']);
$assignmentsSetDiscount = $assignmentsSetDiscount->getData();
if (!$assignmentsSetDiscount['projectid']) finish(false,["message"=>"Cannot find projectid"]);
$DBLIB->where("projects.projects_id", $assignmentsSetDiscount["projectid"]);
$DBLIB->where("projects.instances_id IN (" . implode(",", $AUTH->data['instance_ids']) . ")");
$DBLIB->where("projects.projects_deleted", 0);
$project = $DBLIB->getone("projects",["projects_id","projects_dates_deliver_start","projects_dates_deliver_end"]);
if (!$project) finish(false,["message"=>"Cannot find project"]);

$projectFinanceHelper = new projectFinance();
$priceMaths = $projectFinanceHelper->durationMaths($project['projects_dates_deliver_start'],$project['projects_dates_deliver_end']);
$projectFinanceCacher = new projectFinanceCacher($project['projects_id']);

foreach ($assignmentsSetDiscount["assignments"] as $assignment) {
    $DBLIB->where("assetsAssignments_id", $assignment['assetsAssignments_id']);
    if (!$DBLIB->update("assetsAssignments", ["assetsAssignments_customPrice" => $_POST['assetsAssignments_customPrice']])) finish(false);
    else {
        $bCMS->auditLog("EDIT-DISCOUNT", "assetsAssignments", $assignment['assetsAssignments_customPrice'], $AUTH->data['users_userid'],null, $assignment['projects_id']);

        if ($assignment['assetsAssignments_customPrice'] > 0) {
            $oldPrice = $assignment['assetsAssignments_customPrice'];
        } else {
            $oldPriceChange = 0.0;
            $oldPriceChange += ($priceMaths['days'] * $assignment['assetTypes_dayRate']);
            $oldPriceChange += ($priceMaths['weeks'] * $assignment['assetTypes_weekRate']);
            $oldPrice = round($oldPriceChange, 2, PHP_ROUND_HALF_UP);
        }
        //Remove the old price
        $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', -1*$oldPrice);

        if ($_POST['assetsAssignments_customPrice'] != null) {
            $price = $_POST['assetsAssignments_customPrice'];
        } else {
            //Price is now manually calculated
            $priceChange = 0.0;
            $priceChange += ($priceMaths['days'] * $assignment['assetTypes_dayRate']);
            $priceChange += ($priceMaths['weeks'] * $assignment['assetTypes_weekRate']);
            $price = round($priceChange, 2, PHP_ROUND_HALF_UP);
        }
        //Add the new price
        $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $price);

        if ($assignment['assetsAssignments_discount'] > 0) {
            //If there was already a discount, remove it, then add it again
            $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', -1*($oldPrice-(round(($oldPrice * (1 - ($assignment['assetsAssignments_discount'] / 100))), 2, PHP_ROUND_HALF_UP))));
            $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $price-(round(($price * (1 - ($assignment['assetsAssignments_discount'] / 100))), 2, PHP_ROUND_HALF_UP)));
        }
    }
}
if ($projectFinanceCacher->save()) finish(true);
else finish(false,["message"=>"Finance Cacher Save failed"]);