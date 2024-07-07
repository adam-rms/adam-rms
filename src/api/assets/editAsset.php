<?php
require_once __DIR__ . '/../apiHeadSecure.php';
require_once __DIR__ . '/../../common/libs/bCMS/projectFinance.php';
use Money\Currency;
use Money\Money;
use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;

if (!$AUTH->instancePermissionCheck("ASSETS:EDIT")) die("Sorry - you can't access this page");
$array = [];

foreach ($_POST as $key=>$value) {
    if ($key == "formData") continue;
    if ($value == '') $value = null;
    $array[$key] = $value;
}
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['assets_id']) < 1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$currencies = new ISOCurrencies();
$moneyParser = new DecimalMoneyParser($currencies);
$array['assets_value'] = ($array['assets_value'] == null ? null : $moneyParser->parse($array['assets_value'], $AUTH->data['instance']['instances_config_currency'])->getAmount());
$array['assets_dayRate'] = ($array['assets_dayRate'] == null ? null : $moneyParser->parse($array['assets_dayRate'], $AUTH->data['instance']['instances_config_currency'])->getAmount());
$array['assets_weekRate'] = ($array['assets_weekRate'] == null ? null : $moneyParser->parse($array['assets_weekRate'], $AUTH->data['instance']['instances_config_currency'])->getAmount());

$DBLIB->where("assets_id", $array['assets_id']);
$DBLIB->where("assets.instances_id",$AUTH->data['instance']["instances_id"]);
$DBLIB->join("assetTypes","assets.assetTypes_id=assetTypes.assetTypes_id","LEFT");
$asset = $DBLIB->getone("assets", ['assets.assets_dayRate','assets.assets_tag','assets.assets_weekRate','assets.assets_mass','assets.assets_value','assetTypes.assetTypes_mass','assetTypes.assetTypes_value',"assetTypes.assetTypes_dayRate","assetTypes.assetTypes_weekRate"]);

if (isset($array['assets_tag']) and $array['assets_tag'] != $asset['assets_tag']) {
    $DBLIB->where("assets.instances_id",$AUTH->data['instance']['instances_id']);
    $DBLIB->where("assets.assets_tag", $array['assets_tag']);
    $DBLIB->where("assets.assets_deleted", 0); //Deleted assets can't be restored, so can be used
    $duplicateAssetTag = $DBLIB->getValue ("assets", "count(*)");
    if ($duplicateAssetTag > 0) finish(false,["message" => "Sorry that Asset Tag is a duplicate of one already in your Business"]);
}

$DBLIB->where("assets_id", $array['assets_id']);
$DBLIB->where("assets.instances_id",$AUTH->data['instance']["instances_id"]);
$result = $DBLIB->update("assets", array_intersect_key( $array, array_flip( ['assets_linkedTo','assetTypes_id','assets_notes','assets_tag','asset_definableFields_1','asset_definableFields_2','asset_definableFields_3','asset_definableFields_4','asset_definableFields_5','asset_definableFields_6','asset_definableFields_7','asset_definableFields_8','asset_definableFields_9','asset_definableFields_10','assets_value','assets_dayRate','assets_weekRate','assets_mass'] ) ));
if (!$result) finish(false, ["code" => "UPDATE-FAIL", "message"=> "Could not update asset"]);
else {
    $DBLIB->where("assets_id",$array['assets_id']);
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

        //Add new mass and value
        $projectFinanceCacher->adjust('projectsFinanceCache_mass',($array['assets_mass'] !== null ? $array['assets_mass'] : $asset['assetTypes_mass']),false);
        $projectFinanceCacher->adjust('projectsFinanceCache_value',new Money(($array['assets_value'] !== null ? $array['assets_value'] : $asset['assetTypes_value']), new Currency($AUTH->data['instance']['instances_config_currency'])),false);

        if ($assignment['assetsAssignments_customPrice'] > 0) {
            //Old price stands so ignore it
        } else {
            $oldPrice = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
            $oldPrice = $oldPrice->add((new Money(($asset['assets_dayRate'] !== null ? $asset['assets_dayRate'] : $asset['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['days']));
            $oldPrice = $oldPrice->add((new Money(($asset['assets_weekRate'] !== null ? $asset['assets_weekRate'] : $asset['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['weeks']));
            //Price is now manually calculated
            $price = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
            $price = $price->add((new Money(($array['assets_dayRate'] !== null ? $array['assets_dayRate'] : $asset['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['days']));
            $price = $price->add((new Money(($array['assets_weekRate'] !== null ? $array['assets_weekRate'] : $asset['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMaths['weeks']));

            //Remove the old price
            $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $oldPrice,true);
            $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $price,false);

            if ($assignment['assetsAssignments_discount'] > 0) {
                //If there was already a discount, remove it, then add it again
                $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $oldPrice->subtract($oldPrice->multiply(1 - ($assignment['assetsAssignments_discount'] / 100))),true);
                $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $price->subtract($price->multiply(1 - ($assignment['assetsAssignments_discount'] / 100))), false);
            }
        }
        $projectFinanceCacher->save();
    }
    $bCMS->auditLog("EDIT-ASSET", "assets", json_encode($array), $AUTH->data['users_userid'],null, $array['assets_id']);
    finish(true);
}

/** @OA\Post(
 *     path="/assets/editAsset.php", 
 *     summary="Edit an Asset", 
 *     description="Edits an asset's data  
Requires Instance Permission ASSETS:EDIT
", 
 *     operationId="editAsset", 
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
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *                 @OA\Property(
 *                     property="error", 
 *                     type="array", 
 *                     description="An Array containing an error code and a message",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="formData",
 *         in="query",
 *         description="The data to update the asset with",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="assets_id", 
 *                 type="integer", 
 *                 description="The ID of the asset to update",
 *             ),
 *             @OA\Property(
 *                 property="assets_value", 
 *                 type="number", 
 *                 description="The value of the asset",
 *             ),
 *             @OA\Property(
 *                 property="assets_dayRate", 
 *                 type="number", 
 *                 description="The day rate of the asset",
 *             ),
 *             @OA\Property(
 *                 property="assets_weekRate", 
 *                 type="number", 
 *                 description="The week rate of the asset",
 *             ),
 *             @OA\Property(
 *                 property="assets_tag", 
 *                 type="string", 
 *                 description="The tag of the asset",
 *             ),
 *             @OA\Property(
 *                 property="assets_mass", 
 *                 type="number", 
 *                 description="The weight of the asset",
 *             ),
 *             @OA\Property(
 *                 property="assets_linkedTo", 
 *                 type="integer", 
 *                 description="The ID of the asset this asset is linked to",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_id", 
 *                 type="integer", 
 *                 description="The ID of the asset type",
 *             ),
 *             @OA\Property(
 *                 property="assets_notes", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_1", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_2", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_3", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_4", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_5", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_6", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_7", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_8", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_9", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_10", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *         ),
 *     ), 
 * )
 */