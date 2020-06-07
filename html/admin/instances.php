<?php
require_once __DIR__ . '/common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Instances", "BREADCRUMB" => false];

if (!$AUTH->permissionCheck(20)) die($TWIG->render('404.twig', $PAGEDATA));

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;
if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
else $page = 1;
$DBLIB->pageLimit = 20; //Users per page
$DBLIB->orderBy("instances.instances_id", "ASC");
$DBLIB->where("instances.instances_deleted", 0);
if (strlen($PAGEDATA['search']) > 0) {
	//Search
	$DBLIB->where("(
		instances_name LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%'
		OR instances_address LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%'
		OR instances_phone LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%'
		OR instances_email LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%'
		OR instances_website LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%'
    )");
}
$instances = $DBLIB->arraybuilder()->paginate('instances', $page, ["instances.*"]);
$PAGEDATA['pagination'] = ["page" => $page, "total" => $DBLIB->totalPages];
$PAGEDATA['instances'] = [];
foreach ($instances as $instance) {
	$PAGEDATA['instances'][] = $instance;
}

echo $TWIG->render('instances.twig', $PAGEDATA);
?>
