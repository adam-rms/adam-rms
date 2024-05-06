<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("CMS:CMS_PAGES:EDIT:CUSTOM_DASHBOARDS")) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
$DBLIB->where("cmsPages_deleted",0);
$DBLIB->where("cmsPages_archived",0);
$DBLIB->orderBy("cmsPages_navOrder","ASC");
$DBLIB->orderBy("cmsPages_id","ASC");
$PAGEDATA['CMSPages'] = $DBLIB->get("cmsPages");


$DBLIB->orderBy("instancePositions_rank", "ASC");
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("instancePositions_deleted",0);
$PAGEDATA['positions'] = $DBLIB->get("instancePositions");

echo $TWIG->render('cms/cms_customDashboards.twig', $PAGEDATA);
?>
