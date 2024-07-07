<?php
require_once __DIR__ . '/../../apiHeadSecure.php';
require_once __DIR__ . '/../../../common/libs/bCMS/projectFinance.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_ASSETS:EDIT:DISCOUNT") or !isset($_POST['assetsAssignments'])) die("404");
use Money\Currency;
use Money\Money;

$assignmentsSetDiscount = new assetAssignmentSelector($_POST['assetsAssignments']);
$assignmentsSetDiscount = $assignmentsSetDiscount->getData();
if (!$assignmentsSetDiscount['projectid']) finish(false,["message"=>"Cannot find projectid"]);
$DBLIB->where("projects.projects_id", $assignmentsSetDiscount["projectid"]);
$DBLIB->where("projects.instances_id", $AUTH->data['instance_ids'], 'IN');
$DBLIB->where("projects.projects_deleted", 0);
$project = $DBLIB->getone("projects",["projects_id"]);
if (!$project) finish(false,["message"=>"Cannot find project"]);

$projectFinanceHelper = new projectFinance();
$priceMaths = $projectFinanceHelper->durationMaths($project['projects_id']);
$projectFinanceCacher = new projectFinanceCacher($project['projects_id']);

foreach ($assignmentsSetDiscount["assignments"] as $assignment) {
    $DBLIB->where("assetsAssignments_id", $assignment['assetsAssignments_id']);
    if (!$DBLIB->update("assetsAssignments", ["assetsAssignments_discount" => $_POST['assetsAssignments_discount']])) finish(false);
    else {
        $bCMS->auditLog("EDIT-DISCOUNT", "assetsAssignments", $assignment['assetsAssignments_id'], $AUTH->data['users_userid'],null, $assignment['projects_id']);

        if ($assignment['assetsAssignments_customPrice'] > 0) {
            $price = new Money($assignment['assetsAssignments_customPrice'], new Currency($AUTH->data['instance']['instances_config_currency']));
        } else {
            $price = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
            $price = $price->add((new Money(($assignment['assets_dayRate'] !== null ? $assignment['assets_dayRate'] : $assignment['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['days']));
            $price = $price->add((new Money(($assignment['assets_weekRate'] !== null ? $assignment['assets_weekRate'] : $assignment['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['weeks']));
        }

        if ($assignment['assetsAssignments_discount'] > 0) {
            //If there was already a discount, remove it
            $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $price->subtract($price->multiply(1 - ($assignment['assetsAssignments_discount'] / 100))),true);
        }
        if ($_POST['assetsAssignments_discount'] > 0) {
            //If there is now a discount, set it up
            $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $price->subtract($price->multiply(1 - ($_POST['assetsAssignments_discount'] / 100))),false);
        }
    }
}
if ($projectFinanceCacher->save()) finish(true);
else finish(false,["message"=>"Finance Cacher Save failed"]);

/** @OA\Post(
 *     path="/projects/assets/setDiscount.php", 
 *     summary="Set Asset Assignment Discount", 
 *     description="Set the discount for an asset assignment  
Requires Instance Permission PROJECTS:PROJECT_ASSETS:EDIT:DISCOUNT
", 
 *     operationId="setAssetAssignmentDiscount", 
 *     tags={"project_assets"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="404", 
 *         description="Permission Error",
 *     ), 
 *     @OA\Parameter(
 *         name="assetsAssignments",
 *         in="query",
 *         description="Asset Assignment IDs",
 *         required="true", 
 *         @OA\Schema(
 *             type="array"), 
 *         ), 
 *     @OA\Parameter(
 *         name="assetsAssignments_discount",
 *         in="query",
 *         description="Discount amount",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */