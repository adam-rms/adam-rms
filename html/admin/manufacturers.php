<?php
require_once __DIR__ . '/common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Manufacturers", "BREADCRUMB" => false];

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;

if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
else $page = 1;
$DBLIB->pageLimit = 20; //Users per page
$DBLIB->orderBy("manufacturers_name", "ASC");
$DBLIB->where("(instances_id IS NULL OR instances_id = '" . $AUTH->data['instance']['instances_id'] . "')");
if (strlen($PAGEDATA['search']) > 0) {
	//Search
	$DBLIB->where("(
		manufacturers_name LIKE '%" . $PAGEDATA['search'] . "%'
    )");
}
$PAGEDATA['manufacturers'] = $DBLIB->arraybuilder()->paginate('manufacturers', $page, ["manufacturers.manufacturers_id","manufacturers.manufacturers_name"]);
$PAGEDATA['pagination'] = ["page" => $page, "total" => $DBLIB->totalPages];

echo $TWIG->render('manufacturers.twig', $PAGEDATA);
?>
