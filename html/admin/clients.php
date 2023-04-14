<?php
require_once __DIR__ . '/common/headSecure.php';
use Money\Currency;
use Money\Money;

$PAGEDATA['pageConfig'] = ["TITLE" => "Clients", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck("CLIENTS:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;

if (!isset($_GET['released'])) $PAGEDATA['includeReleased'] = false;
else $PAGEDATA['includeReleased'] = true;

if (isset($_GET['archive'])) {
	$DBLIB->where("clients.clients_archived", 1);
	$PAGEDATA['showArchived'] = true;
} else {
	$DBLIB->where("clients.clients_archived", 0);
	$PAGEDATA['showArchived'] = false;
}

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
	$DBLIB->join("projectsTypes", "projects.projectsTypes_id=projectsTypes.projectsTypes_id");
	$DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
	$DBLIB->where("projectsTypes.projectsTypes_config_finance", 1);
	if (!isset($_GET['future'])) {
		$DBLIB->where("projects.projects_dates_use_end < '" . date('Y-m-d H:i:s'). "'");
		$PAGEDATA['includeFuture'] = false;
	} else $PAGEDATA['includeFuture'] = true;
	$projects = $DBLIB->get("projects", null, ["projects.projects_id","projectsStatuses.projectsStatuses_assetsReleased"]);
	$client['totalPayments'] = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
	$client['totalOutstanding'] = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
	foreach ($projects as $project) {
		$DBLIB->where("projects_id", $project['projects_id']);
		$DBLIB->orderBy("projectsFinanceCache_timestamp", "DESC");
		$project['finance'] = $DBLIB->getOne("projectsFinanceCache");
		if (!$project['finance']) throw new Exception("Project lacks cache error");

		$client['totalPayments'] = $client['totalPayments']->add(new Money($project['finance']['projectsFinanceCache_paymentsReceived'], new Currency($AUTH->data['instance']['instances_config_currency'])));

		if (!$PAGEDATA['includeReleased']) {
			if ($project['projectsStatuses_assetsReleased'] == '1') continue; //Skip this project
		}
		$client['totalOutstanding'] = $client['totalOutstanding']->add(new Money($project['finance']['projectsFinanceCache_grandTotal'], new Currency($AUTH->data['instance']['instances_config_currency'])));
	}

	$PAGEDATA['clients'][] = $client;
}


echo $TWIG->render('clients.twig', $PAGEDATA);
