<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(133)) die("404");

//Joins strings
//"main table" => ["tablename" => ["join"=> join string, "direction" => join direction]]
//this might help: "" => ["join" => "", "direction" => ""],
$joins = [
    "assettypes" => [
        "manufacturers" => ["join" => "assettypes.manufacturers_id=manufacturers.manufacturers_id", "direction" => "LEFT"],
        "assetcategories" =>["join" => "assettypes.assetCategories_id=assetcategories.assetCategories_id", "direction" => "LEFT"],
    ],
    "assets" => [
        "assettypes" => ["join" => "assets.assetTypes_id=assettypes.assetTypes_id", "direction" => "LEFT"],
    ],
    "projects" => [
        "locations" => ["join" => "projects.locations_id=locations.locations_id", "direction" => "LEFT"],
        "clients" => ["join" => "projects.clients_id=clients.clients_id", "direction" => "LEFT"],
        "users" => ["join" => "projects.projects_manager=users.users_userid", "direction" => "LEFT"],
    ]
];

/**
 * Request Data
 * filetype => $_POST['filetype'];
 * table => $_POST['table'];
 * columns => $_POST['columns'];
 * sort => $_POST['sort'];
 * sortDirection => $_POST['direction'];
 * join => $_POST['joins'];
 */
if ($_POST['filetype'] == "csv") {
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=assets.csv");
}
elseif ($_POST['filetype'] == "xlsx") {
    header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment; filename=assets.xlsx");
} else die("404");

header("Pragma: no-cache");
header("Expires: 0");
//error_reporting(0); //Errors if shown mean it won't download right
//ini_set('display_errors', 0);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


//TODO check values before putting them in here
//TODO check instance ID

$DBLIB->orderBy($_POST['sort'], $_POST['direction'] );
if (isset($_POST['joins'])){
    foreach ($_POST['joins'] as $jointable){
        $DBLIB->join($jointable, $joins[$_POST['table']][$jointable]['join'], $joins[$_POST['table']][$jointable]['direction']);
    }
}
$results = $DBLIB->get($_POST['table'], null, $_POST['columns']);
$spreadsheet = new Spreadsheet();
$spreadsheet->getProperties()
    ->setCreator("Bithell Studios Ltd.")
    ->setLastModifiedBy($AUTH->data['instance']['instances_name'])
    ->setCompany($AUTH->data['instance']['instances_name'])
    ->setCreated(date('Y-m-d H:i:s'))
    ->setTitle($_POST['table'] . " Download from AdamRMS")
    ->setSubject("All " . $_POST['table'] . " from " . $AUTH->data['instance']['instances_name']);
$spreadsheet->getActiveSheet()->setTitle($_POST['table'] . " List");
$sheet = $spreadsheet->getActiveSheet();
for ($x = 1; $x < $sheet->getHighestRow(); $x++) {
    $sheet->removeRow($x);
}
$sheet->fromArray($_POST['columns'], NULL, 'A1');
$sheet->fromArray($results, NULL, 'A2');

if ($_POST['filetype'] == "csv") {
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
    $writer->setDelimiter(',');
    $writer->setEnclosure('"');
    $writer->setLineEnding("\r\n");
    $writer->setSheetIndex(0);
    ob_start();
    $writer->save("php://output");
    $csvData = ob_get_contents();
    ob_end_clean();

    $file = array('data' => "data:text/csv;base64,".base64_encode($csvData));
    finish(true, null, $file);
} elseif ($_POST['filetype'] == "xlsx") {
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

    ob_start();
    $writer->save('php://output');
    $xlsData = ob_get_contents();
    ob_end_clean();

    $file = array('data' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,".base64_encode($xlsData));
    finish(true, null, $file);
} else die("404");

