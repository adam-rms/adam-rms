<?php
require_once __DIR__ . '/common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Clients", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck(36)) die($TWIG->render('404.twig', $PAGEDATA));

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;

if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
else $page = 1;
$DBLIB->pageLimit = 20; //Users per page
$DBLIB->where("clients.clients_deleted", 0);
$DBLIB->where("clients.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->orderBy("clients.clients_name", "ASC");
if (strlen($PAGEDATA['search']) > 0) {
	//Search
	$DBLIB->where("(
		clients.clients_name LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%' OR
		clients.clients_address LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%' OR
		clients.clients_email LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%' OR
		clients.clients_website LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%' OR
		clients.clients_phone LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%' OR
		clients.clients_notes LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%' 
    )");
}
$clients = $DBLIB->arraybuilder()->paginate('clients', $page, ["*"]);
$PAGEDATA['pagination'] = ["page" => $page, "total" => $DBLIB->totalPages];

$PAGEDATA['clients'] = [];
foreach ($clients as $client) {
	$DBLIB->where("clients_id", $client['clients_id']);
	$DBLIB->where("projects_deleted", 0);
	if (!isset($_GET['future'])) {
		$DBLIB->where("projects.projects_dates_use_end < '" . date('Y-m-d H:i:s'). "'");
		$PAGEDATA['includeFuture'] = false;
	} else $PAGEDATA['includeFuture'] = true;
	$projects = $DBLIB->get("projects", null, ["projects_id"]);
	$client['totalPayments'] = 0.0;
	$client['totalOutstanding'] = 0.0;
	foreach ($projects as $project) {
		$DBLIB->where("projects_id", $project['projects_id']);
		$DBLIB->orderBy("projectsFinanceCache_timestamp", "DESC");
		$project['finance'] = $DBLIB->getOne("projectsFinanceCache");
		if (!$project['finance']) die('<a href="' . $CONFIG['ROOTURL'] . '/project?id=' . $project['projects_id'] . '" target="_blank">Project</a> lacks cache error');

		$client['totalPayments'] += $project['finance']['projectsFinanceCache_paymentsReceived'];
		$client['totalOutstanding'] += $project['finance']['projectsFinanceCache_grandTotal'];
	}

	$PAGEDATA['clients'][] = $client;
}


echo $TWIG->render('clients.twig', $PAGEDATA);
?>
