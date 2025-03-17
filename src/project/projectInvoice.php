<?php
require_once __DIR__ . '/../common/headSecure.php';
if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") or !isset($_GET['id'])) die($TWIG->render('404.twig', $PAGEDATA));
require_once __DIR__ . '/../api/projects/data.php'; //Where most of the data comes from

$PAGEDATA['GET'] = $_GET;
$PAGEDATA['GET']['generate'] = true;

$isQuote = $_GET['type'] == "quote";
$isDeliveryNote = $_GET['type'] == "deliveryNote";
$PAGEDATA['GET']['quote'] = $isQuote;
$PAGEDATA['GET']['deliveryNote'] = $isDeliveryNote;

$typeId = 20;
$PAGEDATA['GET']['fileType'] = 'Invoice';

switch ($_GET['type']) {
    case 'quote':
        $typeId = 21;
        $PAGEDATA['GET']['fileType'] = 'Quotation';
        break;
    case 'deliveryNote':
        $typeId = 22;
        $PAGEDATA['GET']['fileType'] = 'Delivery Note';
        break;
    default:
        break;
}

$DBLIB->where("s3files_meta_type", $typeId);
$DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
$DBLIB->where("s3files_meta_subType",$_GET['id']);
$count = $DBLIB->getValue ("s3files", "count(*)");
if ($count) $fileNumber = ($count+1);
else $fileNumber = 1;
$PAGEDATA['fileNumber'] = $fileNumber;

if ($PAGEDATA['USERDATA']['instance']['instances_logo'] and $PAGEDATA['GET']['instancelogo']) {
    $PAGEDATA['INSTANCELOGO'] = $bCMS->s3DataUri($PAGEDATA['USERDATA']['instance']['instances_logo']);
} else $PAGEDATA['INSTANCELOGO'] = false;

echo $TWIG->render('project/pdf.twig', $PAGEDATA);