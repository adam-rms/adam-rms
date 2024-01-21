<?php
require_once __DIR__ . '/../apiHeadSecure.php';
if (isset($_POST['csv'])) {
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=assets.csv");
}
elseif (isset($_POST['xlsx'])) {
    header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment; filename=assets.xlsx");
}
header("Pragma: no-cache");
header("Expires: 0");
error_reporting(0); //Errors if shown mean it won't download right
ini_set('display_errors', 0);

use Money\Currency;
use Money\Money;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
$currencies = new ISOCurrencies();
$numberFormatter = new NumberFormatter('en_GB', NumberFormatter::CURRENCY);
$moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$DBLIB->orderBy("assetCategories.assetCategories_id", "ASC");
$DBLIB->orderBy("assetTypes.assetTypes_name", "ASC");
$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->where("((SELECT COUNT(*) FROM assets WHERE assetTypes.assetTypes_id=assets.assetTypes_id AND assets.instances_id = '" . $AUTH->data['instance']['instances_id'] . "' AND assets_deleted = 0) > 0)");
$DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
$assets = $DBLIB->get('assetTypes', null, ["assetTypes.*", "manufacturers.manufacturers_name", "assetCategories.assetCategories_name", "assetCategoriesGroups.assetCategoriesGroups_name"]);
$PAGEDATA['assets'] = [];
$count = 0;
foreach ($assets as $asset) {
    $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("assets.assetTypes_id", $asset['assetTypes_id']);
    $DBLIB->where("assets_deleted", 0);
    $DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
    $DBLIB->orderBy("assets.assets_tag", "ASC");
    $asset['assets'] = $DBLIB->get("assets");
    $count += count($asset['assets']);
    $PAGEDATA['assets'][] = $asset;
}

//Get first asset added to give a created date
$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assets_deleted", 0);
$DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
$DBLIB->orderBy("assets.assets_inserted", "ASC");
$created = $DBLIB->getValue("assets", "assets_inserted");

$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator("Bithell Studios Ltd.")
    ->setLastModifiedBy($AUTH->data['instance']['instances_name'])
    ->setCompany($AUTH->data['instance']['instances_name'])
    ->setCreated(strtotime($created))
    ->setTitle("Asset Download from AdamRMS")
    ->setSubject("All assets from " . $AUTH->data['instance']['instances_name']);
$spreadsheet->getActiveSheet()->setTitle("Assets List");
$sheet = $spreadsheet->getActiveSheet();
for ($x = 1; $x < $sheet->getHighestRow(); $x++) {
    $sheet->removeRow($x);
}
$finalData = [];
foreach ($PAGEDATA['assets'] as $assetType) {
    $assetType['definableFields'] = explode(",", $assetType['assetTypes_definableFields']);
    if (count($assetType['definableFields']) != 10) $assetType['definableFields'] = ["","","","","","","","","","",""];
    foreach ($assetType['assets'] as $asset) {
        $latestScan = assetLatestScan($asset['assets_id']);
        if ($latestScan['locations_name']) $assetLocation = $latestScan['locations_name'];
        else if ($latestScan['assetTypes_name']) $assetLocation = "Inside asset " . $latestScan['assetTypes_name']; //TODO improve this to mean something a bit more
        else if ($latestScan['assetsBarcodes_customLocation']) $assetLocation = $latestScan['assetsBarcodes_customLocation'];

        $spreadsheet->getActiveSheet()->insertNewRowBefore(1, 1);
        $array = [
            $asset['assets_tag'],
            $assetType['assetCategoriesGroups_name'] . " - " . $assetType['assetCategories_name'],
            $assetType['assetTypes_name'],
            ($assetType['manufacturers_id'] != 1 ? $assetType['manufacturers_name'] : ""),
            $asset['assets_mass'] !== null ?  $asset['assets_mass'] : $assetType['assetTypes_mass'],
            $moneyFormatter->format(new Money($asset['assets_dayRate'] !== null ? $asset['assets_dayRate'] : $assetType['assetTypes_dayRate'], new Currency($AUTH->data['instance']['instances_config_currency']))),
            $moneyFormatter->format(new Money($asset['assets_weekRate'] !== null ? $asset['assets_weekRate'] : $assetType['assetTypes_weekRate'], new Currency($AUTH->data['instance']['instances_config_currency']))),
            $moneyFormatter->format(new Money($asset['assets_value'] !== null ? $asset['assets_value'] : $assetType['assetTypes_value'], new Currency($AUTH->data['instance']['instances_config_currency']))),
            $assetLocation,
            $asset['assets_notes']
        ];
        for ($x = 1; $x <= 10; $x++) {
            array_push($array, $assetType['definableFields'][$x-1] . ($assetType['definableFields'][$x-1] != null ? ": ": ""),$asset['asset_definableFields_' . $x]);
        }
        $sheet->fromArray($array, NULL, 'A2');
    }
}
if (isset($_POST['csv'])) {
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
    $writer->setDelimiter(',');
    $writer->setEnclosure('"');
    $writer->setLineEnding("\r\n");
    $writer->setSheetIndex(0);
    $writer->save("php://output");
} elseif (isset($_POST['xlsx'])) {
    //Header
    $sheet->fromArray(["Asset Code", "Category", "Name", "Manufacturer", "Mass (kg)", "Day Rate", "Week Rate", "Value", "Location", "Notes", "Definable Fields"], NULL, 'A1');
    $sheet->mergeCells('J1:AC1');
    $sheet->getStyle("A1:AC1")->getFont()->setBold( true );
    $sheet->freezePane('B2');
    //Number Formats
    $sheet->getStyle('E2:E' . ($count+1))
        ->getNumberFormat()
        ->setFormatCode('#,##0.000');
    $sheet->getStyle('F2:F' . ($count+1))
        ->getNumberFormat()
        ->setFormatCode( '_-£* #,##0.00_-;-£* #,##0.00_-;_-£* "-"??_-;_-@_-');
    $sheet->getStyle('G2:G' . ($count+1))
        ->getNumberFormat()
        ->setFormatCode( '_-£* #,##0.00_-;-£* #,##0.00_-;_-£* "-"??_-;_-@_-');
    $sheet->getStyle('H2:H' . ($count+1))
        ->getNumberFormat()
        ->setFormatCode( '_-£* #,##0.00_-;-£* #,##0.00_-;_-£* "-"??_-;_-@_-');
    $sheet->getStyle('J2:J' . ($count+1))
        ->getAlignment()
        ->setWrapText(true);
    //Column Widths
    foreach(range('A','Z') as $columnID) {
        $sheet->getColumnDimension($columnID)
            ->setAutoSize(true);
    }
    //AutoFilter
    $sheet->setAutoFilter(
        $sheet->calculateWorksheetDimension()
    );

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
} else die("404");

/** @OA\Post(
 *     path="/assets/export.php", 
 *     summary="Export Assets", 
 *     description="Exports assets
", 
 *     operationId="exportAssets", 
 *     tags={"assets"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/csv", 
 *             @OA\Schema( 
 *                 type="string", 
 *                 ),
 *         ),@OA\MediaType(
 *             mediaType="application/xlsx", 
 *             @OA\Schema( 
 *                 type="string", 
 *                 ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="404", 
 *         description="Error",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="string", 
 *                     description="404",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="csv",
 *         in="query",
 *         description="Whether to export as a CSV file",
 *         required="false", 
 *         @OA\Schema(
 *             type="any"), 
 *         ), 
 *     @OA\Parameter(
 *         name="xlsx",
 *         in="query",
 *         description="Whether to export as an XLSX file",
 *         required="false", 
 *         @OA\Schema(
 *             type="any"), 
 *         ), 
 * )
 */