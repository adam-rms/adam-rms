<?php

require_once __DIR__ . '/../apiHeadSecure.php';
ini_set('max_execution_time', 300); //seconds
ini_set('memory_limit','2048M');

require_once __DIR__ . '/../projects/data.php'; //Where most of the data comes from

if (!$AUTH->instancePermissionCheck(20) or !isset($_GET['id'])) finish(false);

$PAGEDATA['GET'] = $_GET;
$PAGEDATA['GET']['generate'] = true;

$DBLIB->where("s3files_meta_type",20);
$DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
$DBLIB->where("s3files_meta_subType",$_GET['id']);
$count = $DBLIB->getValue ("s3files", "count(*)");
if ($count) $fileNumber = ($count+1);
else $fileNumber = 1;
$PAGEDATA['fileNumber'] = $fileNumber;

$mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mpdf', 'mode' => 'utf-8', 'format' => 'A4', 'setAutoTopMargin' => 'pad', "margin_top" => 5]);
$mpdf->SetTitle($PAGEDATA['project']['projects_name'] . ($PAGEDATA['project']['clients_name'] ? " - " . $PAGEDATA['project']['clients_name'] : ''));
$mpdf->SetAuthor($PAGEDATA['USERDATA']['instance']['instances_name']);
$mpdf->SetCreator("AdamRMS - the rental management system from Bithell Studios");
$mpdf->SetSubject($PAGEDATA['project']['projects_name'] . ($PAGEDATA['project']['clients_name'] ? " - " . $PAGEDATA['project']['clients_name'] : '') . " | " . $PAGEDATA['USERDATA']['instance']['instances_name']);
$mpdf->SetKeywords("quotation,invoice,AdamRMS");
$mpdf->SetHTMLFooter('
            <table width="100%">
                <tr>
                    <td width="45%">Generated {DATE j M Y h:i:sa}</td>
                    <td width="10%" align="center">{PAGENO}/{nbpg}</td>
                    <td width="45%" style="text-align: right;">AdamRMS | &copy;{DATE Y} Bithell Studios Ltd.</td>
                </tr>
            </table>
         ');
$mpdf->WriteHTML($TWIG->render('project/pdf.twig', $PAGEDATA));

$filename = "invoice-" . time() . "-" . (floor(rand())) . "." . "pdf";
$fullFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mpdf' . DIRECTORY_SEPARATOR . $filename;
$mpdf->Output($fullFilename, 'F');

$s3 = new Aws\S3\S3Client([
    'version' => 'latest',
    'region' => $CONFIG['AWS']['DEFAULTUPLOADS']['REGION'],
    'endpoint' => "https://" . $CONFIG['AWS']['DEFAULTUPLOADS']['ENDPOINT'],
    'credentials' => array(
        'key' => $CONFIG['AWS']['KEY'],
        'secret' => $CONFIG['AWS']['SECRET'],
    )
]);
$extension = "pdf";
$result = $s3->putObject([
    'Bucket' => $CONFIG['AWS']['DEFAULTUPLOADS']['BUCKET'],
    'Key'    => "uploads/" . "PROJECT_INVOICES" . "/" . $filename,
    'SourceFile' => $fullFilename
]);
$code = $result['@metadata']['statusCode'];
$uri = $result['@metadata']['effectiveUri'];
if ($code === 200) {
    $fileData = [
        "s3files_extension" => "pdf",
        "s3files_path" => "uploads/" . "PROJECT_INVOICES",
        "s3files_region" => $CONFIG['AWS']['DEFAULTUPLOADS']['REGION'],
        "s3files_endpoint" => $CONFIG['AWS']['DEFAULTUPLOADS']['ENDPOINT'],
        "s3files_bucket" => $CONFIG['AWS']['DEFAULTUPLOADS']['BUCKET'],
        "s3files_meta_size" => filesize($fullFilename),
        "s3files_meta_type" => 20,
        "s3files_meta_subType" => $_GET['id'],
        "users_userid" => $AUTH->data['users_userid'],
        "s3files_original_name" => "invoice.pdf",
        "s3files_filename" => pathinfo($filename, PATHINFO_FILENAME),
        "s3files_name" => "v" . $fileNumber,
        "s3files_cdn_endpoint" => $CONFIG['AWS']['DEFAULTUPLOADS']['CDNEndpoint'],
        "s3files_meta_public" => 0,
        "instances_id" => $AUTH->data['instance']['instances_id']
    ];
    $id = $DBLIB->insert("s3files",$fileData);
    echo $DBLIB->getLastError();
    if (!$id) finish(false, ["code" => null, "message" => "Error"]);
    else finish(true, null, ["id" => $id,"url" => $CONFIG['ROOTURL'] . '/api/file/?r&f=' . $id]);
} else finish(false,["message"=>"Upload to S3 Failed"]);
