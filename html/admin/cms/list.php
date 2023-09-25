<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("CMS:CMS_PAGES:CREATE")) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['includeArchived'] = (isset($_GET['archive']) and $_GET['archive'] == 1 ? true : false);

$PAGEDATA['pageConfig'] = ["TITLE" => ($PAGEDATA['includeArchived']? "Archived ": "") . "CMS Pages", "BREADCRUMB" => false];

$DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
$DBLIB->where("cmsPages_deleted",0);
$DBLIB->where("cmsPages_archived",($PAGEDATA['includeArchived'] ? 1: 0));
$DBLIB->where("cmsPages_subOf",NULL,"IS");
$DBLIB->orderBy("cmsPages_navOrder","ASC");
$DBLIB->orderBy("cmsPages_id","ASC");
$cmsPages = $DBLIB->get("cmsPages", null, "cmsPages.*, (SELECT count(*) FROM cmsPagesViews WHERE cmsPagesViews.cmsPages_id = cmsPages.cmsPages_id) as views");
$PAGEDATA['CMSPages'] = [];
$PAGEDATA['CMSPagesAll'] = [];
foreach ($cmsPages as $page) {
    $DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
    $DBLIB->where("cmsPages_deleted",0);
    $DBLIB->where("cmsPages_archived",0);
    $DBLIB->where("cmsPages_subOf",$page['cmsPages_id']);
    $DBLIB->orderBy("cmsPages_name","ASC");
    $DBLIB->orderBy("cmsPages_id","ASC");
    $page['SUBPAGES'] = $DBLIB->get("cmsPages", null, "cmsPages.*, (SELECT count(*) FROM cmsPagesViews WHERE cmsPagesViews.cmsPages_id = cmsPages.cmsPages_id) as views");
    foreach ($page['SUBPAGES'] as $subPage) {
        $PAGEDATA['CMSPagesAll'][] = $subPage;
    }
    $PAGEDATA['CMSPages'][] = $page;
    $PAGEDATA['CMSPagesAll'][] = $page;
}

$DBLIB->orderBy("instancePositions_rank", "ASC");
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("instancePositions_deleted",0);
$PAGEDATA['positions'] = $DBLIB->get("instancePositions");


$PAGEDATA['publicData'] = json_decode($AUTH->data['instance']['instances_publicConfig'],true);

echo $TWIG->render('cms/cms_list.twig', $PAGEDATA);
?>
