<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck(103)) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("locations.locations_deleted", 0);
$DBLIB->join("clients","locations.clients_id=clients.clients_id","LEFT");
$DBLIB->where("locations.locations_id",$_GET['location']);
$PAGEDATA['location'] = $DBLIB->getOne('locations', ["locations.*", "clients.clients_name"]);
if (!$PAGEDATA['location']) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->where("locations_id",$PAGEDATA['location']['locations_id']);
$DBLIB->where("locationsBarcodes_deleted",0);
$PAGEDATA['barcode'] = $DBLIB->getone("locationsBarcodes");
if (!$PAGEDATA['barcode']) {
    $locationBarcodeData = [
        "locationsBarcodes_value" => "L" . $PAGEDATA['location']['locations_id'],
        "locationsBarcodes_type" => "CODE_128",
        "locations_id" => $PAGEDATA['location']['locations_id'],
        "users_userid" => $AUTH->data['users_userid'],
        "locationsBarcodes_added" => date("Y-m-d H:i:s")
    ];
    $insert = $DBLIB->insert("locationsBarcodes", $locationBarcodeData);
    $locationBarcodeData['locationsBarcodes_id'] = $insert;
    $PAGEDATA['barcode'] = $locationBarcodeData;
}


$mpdf = new \Mpdf\Mpdf([
    'tempDir' => sys_get_temp_dir().DIRECTORY_SEPARATOR.'mpdf',
    'mode' => 'utf-8',
    'format' => 'A5',
    'setAutoTopMargin' => 'pad',
    "orientation" => "P",
    'margin_footer' => 0,
    'margin_header' => 0
]);
$mpdf->SetTitle("Location Barcode - " . $PAGEDATA['location']['locations_name']);
$mpdf->shrink_tables_to_fit = 1;
$mpdf->SetAuthor($PAGEDATA['USERDATA']['instance']['instances_name']);
$mpdf->SetCreator("AdamRMS - the rental management system from Bithell Studios");
$mpdf->SetSubject("Barcode for " . $PAGEDATA['location']['locations_name']);
$mpdf->SetKeywords("AdamRMS");
$mpdf->SetHTMLFooter('
            <table width="100%" style="font-size: 7pt">
                <tr>
                    <td width="50%">Generated {DATE j M Y h:i:sa}</td>
                    <td width="50%" style="text-align: right;">AdamRMS | &copy;{DATE Y} Bithell Studios Ltd.</td>
                </tr>
            </table>
         ');
$mpdf->WriteHTML($TWIG->render('location/location_barcode.twig', $PAGEDATA));
$mpdf->Output(mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', ("Barcode for " . $PAGEDATA['location']['locations_name'])). '.pdf', (isset($_GET['download']) ? 'D' : 'I'));
?>
