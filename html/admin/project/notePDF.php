<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck(20) or !isset($_GET['id'])) die("Sorry - you can't access this page");

$DBLIB->where("projectsNotes_deleted", 0);
$DBLIB->where("projectsNotes_id", $_GET['id']);
$PAGEDATA['note'] = $DBLIB->getone("projectsNotes");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $PAGEDATA['note']['projects_id']);
$PAGEDATA['project'] = $DBLIB->getone("projects", ["projects.*"]);
if (!$PAGEDATA['project']) die("404");

$mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir().DIRECTORY_SEPARATOR.'mpdf','mode' => 'utf-8', 'format' => 'A4', 'setAutoTopMargin' => 'pad', "margin_top" => 5]);
$mpdf->SetTitle($PAGEDATA['project']['projects_name'] . " - ". $PAGEDATA['note']['projectsNotes_title']);
$mpdf->SetAuthor($PAGEDATA['USERDATA']['instance']['instances_name']);
$mpdf->SetCreator("AdamRMS - the rental management system from Bithell Studios");
$mpdf->SetSubject($PAGEDATA['project']['projects_name'] . " - ". $PAGEDATA['note']['projectsNotes_title'] . " | " . $PAGEDATA['USERDATA']['instance']['instances_name']);
$mpdf->SetKeywords("AdamRMS");
$mpdf->SetHTMLFooter('
            <table width="100%">
                <tr>
                    <td width="45%">Exported {DATE j M Y h:i:sa}</td>
                    <td width="10%" align="center">{PAGENO}/{nbpg}</td>
                    <td width="45%" style="text-align: right;">AdamRMS | &copy;{DATE Y} Bithell Studios Ltd.</td>
                </tr>
            </table>
         ');
$mpdf->WriteHTML($TWIG->render('project/pdf-note.twig', $PAGEDATA));
$mpdf->Output(mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', ($PAGEDATA['project']['projects_name'] . " - ". $PAGEDATA['note']['projectsNotes_title']. " - " . $PAGEDATA['USERDATA']['instance']['instances_name'])). '.pdf', 'I');

?>
