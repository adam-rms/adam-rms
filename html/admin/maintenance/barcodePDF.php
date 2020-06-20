<?php
ini_set('max_execution_time', 300); //seconds
require_once __DIR__ . '/../common/headSecure.php';
if (!$AUTH->instancePermissionCheck(84) or !isset($_GET['ids'])) die($TWIG->render('404.twig', $PAGEDATA));
error_reporting(E_ERROR | E_PARSE);
$ids = explode(",",$_GET['ids']);
$PAGEDATA['assets'] = [];
foreach ($ids as $id) {
    if ($id == null) continue;
    $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
    $DBLIB->where("assets.assets_deleted", 0);
    $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("assets.assets_id",$id);
    $asset = $DBLIB->getOne("assets", null, ["assets.assets_id", "assetTypes.assetTypes_definableFields", "assets.asset_definableFields_1", "assetTypes.assetTypes_name", "asset.assets_mass", "assetTypes.assetTypes_mass","assets.assets_tag", "manufacturers.manufacturers_name"]);
    if ($asset) {
        $DBLIB->orderBy("assetsBarcodes.assetsBarcodes_added","DESC");
        $DBLIB->where("assetsBarcodes.assetsBarcodes_type","CODE_128");
        $DBLIB->where("assetsBarcodes.assetsBarcodes_deleted",0);
        $DBLIB->where("assetsBarcodes.assets_id",$asset['assets_id']);
        $asset['barcode'] = $DBLIB->getone("assetsBarcodes");
        if (!$asset['barcode']) {
            $assetBarcodeData = [
                "assetsBarcodes_value" => $asset['assets_id'] . $asset['assets_tag'],
                "assetsBarcodes_type" => "CODE_128",
                "assets_id" => $asset['assets_id'],
                "users_userid" => $AUTH->data['users_userid'],
                "assetsBarcodes_added" => date("Y-m-d H:i:s")
            ];
            $insert = $DBLIB->insert("assetsBarcodes", $assetBarcodeData);
            if ($insert) {
                $assetBarcodeData['assetsBarcodes_id'] = $insert;
                $asset['barcode'] = $assetBarcodeData;
            } else continue; //Don't handle this asset
        }

        $asset['assetTypes_definableFields'] = explode(",",$asset['assetTypes_definableFields']);
        $PAGEDATA['assets'][] = $asset;
    }
}
$PAGEDATA['GET'] = $_GET;
if ($_GET['numStickers'] == 8) {
    $margins = [
        "top" => 13,
        "bottom" => 10,
        "left" => 5,
        "right" => 5
    ];
} else {
    $margins = [
        "top" => 5,
        "bottom" => 5,
        "left" => 5,
        "right" => 5
    ];
}
//die($TWIG->render('maintenance/barcodePDF.twig', $PAGEDATA));
$mpdf = new \Mpdf\Mpdf([
        'tempDir' => sys_get_temp_dir().DIRECTORY_SEPARATOR.'mpdf',
        'mode' => 'utf-8',
        'format' => $_GET['pagesize'],
        'setAutoTopMargin' => 'pad',
        "orientation" => "P",
        'margin_footer' => 0,
        'margin_header' => 0,
        "margin_top" => $margins['top'],
        "margin_bottom" => $margins['bottom'],
        "margin_left" => $margins['left'],
        "margin_right" => $margins['right'],
    ]);
$mpdf->SetTitle("Asset Barcodes");
$mpdf->shrink_tables_to_fit = 1;
//$mpdf->SetColumns(2, null ,4);
$mpdf->SetAuthor($PAGEDATA['USERDATA']['instance']['instances_name']);
$mpdf->SetCreator("AdamRMS - the rental management system from Bithell Studios");
$mpdf->SetSubject("Barcodes" . " | " . $PAGEDATA['USERDATA']['instance']['instances_name']);
$mpdf->SetKeywords("AdamRMS");
$mpdf->SetHTMLFooter('
            <table width="100%" style="font-size: 7pt">
                <tr>
                    <td width="45%">Generated {DATE j M Y h:i:sa}</td>
                    <td width="10%" align="center">{PAGENO}/{nbpg}</td>
                    <td width="45%" style="text-align: right;">AdamRMS | &copy;{DATE Y} Bithell Studios Ltd.</td>
                </tr>
            </table>
         ');
$mpdf->WriteHTML($TWIG->render('maintenance/barcodePDF.twig', $PAGEDATA));
$mpdf->Output(mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', ("Asset Barcodes - " . $PAGEDATA['USERDATA']['instance']['instances_name'])). '.pdf', 'D');//I

?>
