<?php
require_once __DIR__ . '/../apiHeadSecure.php';
require_once __DIR__ . '/../../common/libs/bCMS/projectFinance.php';
use Money\Currency;
use Money\Money;
use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;
if (!$AUTH->instancePermissionCheck("ASSETS:DELETE")) die("Sorry - you can't access this page");

if (!isset($_POST['assets_id'])) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assets.assets_id", $_POST['assets_id']);
$DBLIB->where("assets.assets_deleted", 0);
$DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
$DBLIB->join("assetTypes","assets.assetTypes_id=assetTypes.assetTypes_id","LEFT");
$asset = $DBLIB->getone("assets", ['assets_id','assets.assets_dayRate','assets.assets_weekRate','assets.assets_mass','assets.assets_value','assetTypes.assetTypes_mass','assetTypes.assetTypes_value',"assetTypes.assetTypes_dayRate","assetTypes.assetTypes_weekRate"]);

if (!$asset) finish(false, ["code" => "DELETE-FAIL", "message"=> "Could not find asset"]);

$DBLIB->where("assets_id", $_POST['assets_id']);
$result = $DBLIB->update("assets", ["assets_deleted" => 1]);
if (!$result) finish(false, ["code" => "DELETE-FAIL", "message"=> "Could not delete asset"]);
else {
    $currencies = new ISOCurrencies();
    $moneyParser = new DecimalMoneyParser($currencies);

    $DBLIB->where("assets_id",$asset['assets_id']);
    $DBLIB->where("assetsAssignments_deleted",0);
    $DBLIB->join("projects","assetsAssignments.projects_id=projects.projects_id","LEFT");
    $assetAssignments = $DBLIB->get("assetsAssignments",null,['projects.projects_id','assetsAssignments_id','assetsAssignments_customPrice','assetsAssignments_discount']);
    foreach ($assetAssignments as $assignment) {
        $projectFinanceHelper = new projectFinance();
        $priceMaths = $projectFinanceHelper->durationMaths($assignment['projects_id']);
        $projectFinanceCacher = new projectFinanceCacher($assignment['projects_id']);

        //Remove current mass and value
        $projectFinanceCacher->adjust('projectsFinanceCache_mass',($asset['assets_mass'] !== null ? $asset['assets_mass'] : $asset['assetTypes_mass']),true);
        $projectFinanceCacher->adjust('projectsFinanceCache_value',new Money(($asset['assets_value'] !== null ? $asset['assets_value'] : $asset['assetTypes_value']), new Currency($AUTH->data['instance']['instances_config_currency'])),true);

        if ($assignment['assetsAssignments_customPrice'] > 0) {
            $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal',$assignment['assetsAssignments_customPrice'],true);
        } else {
            $oldPrice = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
            $oldPrice = $oldPrice->add((new Money(($asset['assets_dayRate'] !== null ? $asset['assets_dayRate'] : $asset['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['days']));
            $oldPrice = $oldPrice->add((new Money(($asset['assets_weekRate'] !== null ? $asset['assets_weekRate'] : $asset['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['weeks']));
            $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $oldPrice,true);
            if ($assignment['assetsAssignments_discount'] > 0) {
                //If there was already a discount, remove it
                $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $oldPrice->subtract($oldPrice->multiply(1 - ($assignment['assetsAssignments_discount'] / 100))),true);
            }
        }
        $projectFinanceCacher->save();
    }
    $bCMS->auditLog("DELETE", "assets", $asset['assets_id'], $AUTH->data['users_userid']);
    finish(true);
}

/** @OA\Post(
 *     path="/assets/delete.php", 
 *     summary="Delete an Asset", 
 *     description="Deletes an asset  
Requires Instance Permission ASSETS:DELETE
", 
 *     operationId="deleteAsset", 
 *     tags={"assets"}, 
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
 *                 @OA\Property(
 *                     property="message", 
 *                     type="null", 
 *                     description="an empty array",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="default", 
 *         description="Error",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="assets_id",
 *         in="query",
 *         description="The ID of the asset to delete",
 *         required="true", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 * )
 */