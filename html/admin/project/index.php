<?php
if (isset($_GET['pdf'])) ini_set('max_execution_time', 300); //seconds
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck(20) or !isset($_GET['id'])) die("Sorry - you can't access this page");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_GET['id']);
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->join("users", "projects.projects_manager=users.users_userid", "LEFT");
$PAGEDATA['project'] = $DBLIB->getone("projects", ["projects.*", "clients.*", "users.users_userid", "users.users_name1", "users.users_name2", "users.users_email"]);
if (!$PAGEDATA['project']) die("404");

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
if ($AUTH->instancePermissionCheck(23)) {
    $DBLIB->orderBy("users.users_name1", "ASC");
    $DBLIB->orderBy("users.users_name2", "ASC");
    $DBLIB->orderBy("users.users_created", "ASC");
    $DBLIB->where("users_deleted", 0);
    $DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
    $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
    $DBLIB->where("instances_id",  $AUTH->data['instance']['instances_id']);
    $DBLIB->where("userInstances.userInstances_deleted",  0);
    $PAGEDATA['potentialManagers'] = $DBLIB->get('users', null, ["users.users_name1", "users.users_name2", "users.users_userid"]);
}

//Payments
$PAGEDATA['FINANCIALS'] = projectFinancials($PAGEDATA['project']['projects_id']);
usort($PAGEDATA['FINANCIALS']['payments']['subHire']['ledger'], function ($a, $b) {
    // Sort sub-hires in order of supplier so you can do supplier headings
    return $a['payments_supplier'] <=> $b['payments_supplier'];
});
usort($PAGEDATA['FINANCIALS']['payments']['sales']['ledger'], function ($a, $b) {
    // Sort sub-hires in order of supplier so you can do supplier headings
    return $a['payments_supplier'] <=> $b['payments_supplier'];
});
usort($PAGEDATA['FINANCIALS']['payments']['staff']['ledger'], function ($a, $b) {
    // Sort sub-hires in order of supplier so you can do supplier headings
    return $a['payments_supplier'] <=> $b['payments_supplier'];
});

//Notes
$DBLIB->where("projectsNotes_deleted", 0);
$DBLIB->where("projects_id", $PAGEDATA['project']['projects_id']);
$DBLIB->orderBy("projectsNotes_id", "ASC");
$PAGEDATA['project']['notes'] = $DBLIB->get("projectsNotes");

//Crew
$DBLIB->where("projects_id", $PAGEDATA['project']['projects_id']);
$DBLIB->where("crewAssignments.crewAssignments_deleted", 0);
$DBLIB->join("users", "crewAssignments.users_userid=users.users_userid", "LEFT");
$DBLIB->orderBy("crewAssignments.crewAssignments_rank", "ASC");
$DBLIB->orderBy("crewAssignments.crewAssignments_id", "ASC");
$PAGEDATA['project']['crewAssignments'] = $DBLIB->get("crewAssignments", null, ["crewAssignments.*", "users.users_name1", "users.users_name2", "users.users_email"]);

if (isset($_GET['loadingView'])) {
    $PAGEDATA['loadingView'] = true;
    $PAGEDATA['loadingViewStatusID'] = (isset($_GET['loadingViewStatus']) ? $_GET['loadingViewStatus'] : 5);
    $PAGEDATA['loadingViewStatus'] = $GLOBALS['ASSETASSIGNMENTSTATUSES'][$PAGEDATA['loadingViewStatusID']];
    $PAGEDATA['loadingViewStatusArray'] = $GLOBALS['ASSETASSIGNMENTSTATUSES'];
}
else $PAGEDATA['loadingView'] = false;

if (isset($_GET['pdf'])) {
    if (isset($_GET['finance'])) $PAGEDATA['showFinance'] = true;

    //die($TWIG->render('project/pdf.twig', $PAGEDATA));
    $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir().DIRECTORY_SEPARATOR.'mpdf','mode' => 'utf-8', 'format' => 'A4', 'setAutoTopMargin' => 'pad', "margin_top" => 5]);
    $mpdf->SetTitle($PAGEDATA['project']['projects_name'] . " - ". $PAGEDATA['project']['clients_name']);
    $mpdf->SetAuthor($PAGEDATA['USERDATA']['instance']['instances_name']);
    $mpdf->SetCreator("AdamRMS - the rental management system from Bithell Studios");
    $mpdf->SetSubject($PAGEDATA['project']['projects_name'] . " - ". $PAGEDATA['project']['clients_name'] . " | " . $PAGEDATA['USERDATA']['instance']['instances_name']);
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
    $mpdf->Output(mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', ($PAGEDATA['project']['projects_name'] . " - ". $PAGEDATA['project']['clients_name'] . " - " . $PAGEDATA['USERDATA']['instance']['instances_name'])). '.pdf', 'I');
} else echo $TWIG->render('project/project_index.twig', $PAGEDATA);
?>
