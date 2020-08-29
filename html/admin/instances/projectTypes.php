<?php
require_once __DIR__ . '/../common/headSecure.php';
use Money\Currency;
use Money\Money;

$PAGEDATA['pageConfig'] = ["TITLE" => "Project Types", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck(105)) die($TWIG->render('404.twig', $PAGEDATA));

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;

$DBLIB->where("projectsTypes_deleted", 0);
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->orderBy("projectsTypes_name", "ASC");
if (strlen($PAGEDATA['search']) > 0) {
    //Search
    $DBLIB->where("(
		projectsTypes_name LIKE '%" . $bCMS->sanitizeString($PAGEDATA['search']) . "%' 
    )");
}
$PAGEDATA['types'] = $DBLIB->get("projectsTypes");

echo $TWIG->render('instances/projectTypes.twig', $PAGEDATA);
?>
