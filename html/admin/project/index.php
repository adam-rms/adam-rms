<?php
if (isset($_GET['pdf'])) ini_set('max_execution_time', 300); //seconds
ini_set('memory_limit','2048M');
require_once __DIR__ . '/../common/headSecure.php';
if (!$AUTH->instancePermissionCheck(20) or !isset($_GET['id'])) die($TWIG->render('404.twig', $PAGEDATA));

require_once __DIR__ . '/../api/projects/data.php'; //Where most of the data comes from


//AuditLog
$DBLIB->where("auditLog.auditLog_deleted", 0);
$DBLIB->where("auditLog.projects_id", $PAGEDATA['project']['projects_id']);
$DBLIB->where("auditLog.auditLog_actionTable", "projects"); //TODO show more in the log but for now only project stuff
$DBLIB->join("users", "auditLog.users_userid=users.users_userid", "LEFT");
$DBLIB->orderBy("auditLog.auditLog_timestamp", "DESC");
$DBLIB->orderBy("auditLog.auditLog_id", "DESC");
$PAGEDATA['project']['auditLog'] = $DBLIB->get("auditLog",null, ["auditLog.*", "users.users_name1", "users.users_name2", "users.users_email"]);

$PAGEDATA['pageConfig'] = ["TITLE" => $PAGEDATA["project"]['projects_name'], "BREADCRUMB" => false];

//Edit Options
if ($AUTH->instancePermissionCheck(22)) {
    $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
    $PAGEDATA['clients'] = $DBLIB->get("clients", null, ["clients_id", "clients_name"]);
}



if (isset($_GET['list']) and $PAGEDATA['project']['projectsTypes_config_assets'] == 1 and (count($PAGEDATA['FINANCIALS']['assetsAssigned'])>0 or count($PAGEDATA['FINANCIALS']['assetsAssignedSUB'])>0)) echo $TWIG->render('project/project_assets.twig', $PAGEDATA);
elseif (isset($_GET['files']) and $PAGEDATA['project']['projectsTypes_config_files'] == 1) echo $TWIG->render('project/project_files.twig', $PAGEDATA);
elseif (isset($_GET['pdf']) and $_GET['pdf']) {
    $PAGEDATA['GET'] = $_GET;
    if (isset($_GET['generate'])) {
        $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mpdf', 'mode' => 'utf-8', 'format' => 'A4', 'setAutoTopMargin' => 'pad', "margin_top" => 5]);
        $mpdf->SetTitle($PAGEDATA['project']['projects_name'] . ($PAGEDATA['project']['clients_name'] ? " - " . $PAGEDATA['project']['clients_name'] : ''));
        $mpdf->SetAuthor($PAGEDATA['USERDATA']['instance']['instances_name']);
        $mpdf->SetCreator("AdamRMS - the rental management system from Bithell Studios");
        $mpdf->SetSubject($PAGEDATA['project']['projects_name'] . ($PAGEDATA['project']['clients_name'] ? " - " . $PAGEDATA['project']['clients_name'] : '') . " | " . $PAGEDATA['USERDATA']['instance']['instances_name']);
        $mpdf->SetKeywords("quotation,AdamRMS");
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
        $mpdf->Output(mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', ($PAGEDATA['project']['projects_name'] . " - " . $PAGEDATA['project']['clients_name'] . " - " . $PAGEDATA['USERDATA']['instance']['instances_name'])) . '.pdf', 'I');
    } else die($TWIG->render('project/pdf.twig', $PAGEDATA));
} else echo $TWIG->render('project/project_index.twig', $PAGEDATA);
?>
