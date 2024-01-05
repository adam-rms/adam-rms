<?php
require_once __DIR__ . '/common/headSecure.php';
use Money\Currency;
use Money\Money;
$PAGEDATA['pageConfig'] = ["TITLE" => "Ledger", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck("FINANCE:PAYMENTS_LEDGER:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;

if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
else $page = 1;
$DBLIB->pageLimit = 20; //Users per page
$DBLIB->join("projects", "payments.projects_id=projects.projects_id", "LEFT");
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->where("projects.instances_id",$AUTH->data['instance']['instances_id']);
$DBLIB->where("payments.payments_deleted", 0);
$DBLIB->where("payments.payments_type", 1);
$DBLIB->orderBy("payments.payments_date", "DESC");
if (strlen($PAGEDATA['search']) > 0) {
	//Search
	$DBLIB->where("(
		payments.payments_reference LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%' OR
		payments.payments_amount LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%' 
    )");
}
$payments = $DBLIB->arraybuilder()->paginate('payments', $page, ["payments.*", "projects.projects_id", "projects.projects_name","clients.clients_name"]);
$PAGEDATA['pagination'] = ["page" => $page, "total" => $DBLIB->totalPages];

$PAGEDATA['payments'] = [];
foreach ($payments as $payment) {
	$payment['payments_amount'] = new Money($payment['payments_amount'], new Currency($AUTH->data['instance']['instances_config_currency']));
	$payment['files'] = $bCMS->s3List(14, $payment['payments_id']);
	$PAGEDATA['payments'][] = $payment;
}
echo $TWIG->render('ledger.twig', $PAGEDATA);
?>
