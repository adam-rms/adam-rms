<?php
require_once __DIR__ . '/../apiHeadSecure.php';
if (isset($_POST['csv'])) {
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=assets.csv");
} elseif (isset($_POST['xlsx'])) {
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


$DBLIB->orderBy("assetCategories.assetCategories_id", "ASC");
$DBLIB->orderBy("assetTypes.assetTypes_name", "ASC");
$DBLIB->orderBy("assets.assets_tag", "ASC");
$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assets_deleted", 0);
$DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
$DBLIB->join("assetTypes", "assetTypes.assetTypes_id=assets.assetTypes_id", "LEFT");
$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
$assets = $DBLIB->get('assets', null, [
    "assets.assets_id", "assets.assets_tag", "assets.assets_mass", "assets.assets_dayRate", "assets.assets_weekRate", "assets.assets_value", "assets.assets_notes", "assets.asset_definableFields_1", "assets.asset_definableFields_2", "assets.asset_definableFields_3", "assets.asset_definableFields_4", "assets.asset_definableFields_5", "assets.asset_definableFields_6", "assets.asset_definableFields_7", "assets.asset_definableFields_8", "assets.asset_definableFields_9", "assets.asset_definableFields_10", "assetTypes.assetTypes_name", "assetTypes.assetTypes_mass", "assetTypes.assetTypes_dayRate", "assetTypes.assetTypes_weekRate", "assetTypes.assetTypes_value", "assetTypes_definableFields", "manufacturers.manufacturers_name", "assetCategories.assetCategories_name", "assetCategoriesGroups.assetCategoriesGroups_name"
]);
//Get first asset added to give a created date
$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assets_deleted", 0);
$DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
$DBLIB->orderBy("assets.assets_inserted", "ASC");
$created = $DBLIB->getValue("assets", "assets_inserted");

// Gather the data for the rows.
$spreadsheetRows = [];
foreach ($assets as $asset) {
    $asset['definableFields'] = explode(",", $asset['assetTypes_definableFields']);
    if (count($asset['definableFields']) != 10) $asset['definableFields'] = ["", "", "", "", "", "", "", "", "", "", ""];

    $latestScan = assetLatestScan($asset['assets_id']);

    if ($latestScan['locations_name']) $assetLocation = $latestScan['locations_name'];
    else if ($latestScan['assetTypes_name']) $assetLocation = "Inside asset " . $latestScan['assetTypes_name']; //TODO improve this to mean something a bit more
    else if ($latestScan['assetsBarcodes_customLocation']) $assetLocation = $latestScan['assetsBarcodes_customLocation'];

    $array = [
        $asset['assets_tag'],
        $asset['assetCategoriesGroups_name'] . " - " . $asset['assetCategories_name'],
        $asset['assetTypes_name'],
        ($asset['manufacturers_id'] != 1 ? $asset['manufacturers_name'] : ""),
        $asset['assets_mass'] !== null ?  $asset['assets_mass'] : $asset['assetTypes_mass'],
        $moneyFormatter->format(new Money($asset['assets_dayRate'] !== null ? $asset['assets_dayRate'] : $asset['assetTypes_dayRate'], new Currency($AUTH->data['instance']['instances_config_currency']))),
        $moneyFormatter->format(new Money($asset['assets_weekRate'] !== null ? $asset['assets_weekRate'] : $asset['assetTypes_weekRate'], new Currency($AUTH->data['instance']['instances_config_currency']))),
        $moneyFormatter->format(new Money($asset['assets_value'] !== null ? $asset['assets_value'] : $asset['assetTypes_value'], new Currency($AUTH->data['instance']['instances_config_currency']))),
        $assetLocation,
        $asset['assets_notes']
    ];
    for ($x = 1; $x <= 10; $x++) {
        array_push($array, $asset['definableFields'][$x - 1] . ($asset['definableFields'][$x - 1] != null ? ": " : ""), $asset['asset_definableFields_' . $x]);
    }
    $spreadsheetRows[] = $array;
}

$headerRow = ["Asset Code", "Category", "Name", "Manufacturer", "Mass (kg)", "Day Rate", "Week Rate", "Value", "Location", "Notes"];
for ($x = 1; $x <= 10; $x++) {
    array_push($headerRow, "Definable Field " . $x . " Name", "Definable Field " . $x . " Value");
}


if (isset($_POST['csv'])) {
    $fp = fopen('php://output', 'w');
    fputcsv($fp, $headerRow, ",", "\"", "\\", "\r\n");
    foreach ($spreadsheetRows as $row) {
        fputcsv($fp, $row, ",", "\"", "\\", "\r\n");
    }
    fclose($fp);
} elseif (isset($_POST['xlsx'])) {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
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

    $spreadsheet->getActiveSheet()->insertNewRowBefore(1, count($assets)); // This is a memory intensive command 
    foreach ($spreadsheetRows as $count => $row) {
        $sheet->fromArray($row, NULL, 'A' . ($count + 2));
    }

    //Header
    $sheet->fromArray($headerRow, NULL, 'A1');
    $sheet->getStyle("A1:AD1")->getFont()->setBold(true);
    $sheet->freezePane('B2');
    //Number Formats
    $sheet->getStyle('E2:E' . (count($assets) + 1))
        ->getNumberFormat()
        ->setFormatCode('#,##0.000');
    $sheet->getStyle('F2:F' . (count($assets) + 1))
        ->getNumberFormat()
        ->setFormatCode('_-£* #,##0.00_-;-£* #,##0.00_-;_-£* "-"??_-;_-@_-');
    $sheet->getStyle('G2:G' . (count($assets) + 1))
        ->getNumberFormat()
        ->setFormatCode('_-£* #,##0.00_-;-£* #,##0.00_-;_-£* "-"??_-;_-@_-');
    $sheet->getStyle('H2:H' . (count($assets) + 1))
        ->getNumberFormat()
        ->setFormatCode('_-£* #,##0.00_-;-£* #,##0.00_-;_-£* "-"??_-;_-@_-');
    $sheet->getStyle('J2:J' . (count($assets) + 1))
        ->getAlignment()
        ->setWrapText(true);
    //Column Widths
    foreach (range('A', 'Z') as $columnID) {
        $sheet->getColumnDimension($columnID)
            ->setAutoSize(true);
    }
    //AutoFilter
    $sheet->setAutoFilter(
        $sheet->calculateWorksheetDimension()
    );

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
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
