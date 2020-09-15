<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Permissions", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck(11)) die("Sorry - you can't access this page");


$DBLIB->orderBy("instanceActionsCategories_order", "ASC");
$PAGEDATA['actionsCategories'] = $DBLIB->get("instanceActionsCategories");

$PAGEDATA['actions'] = [];
foreach ($PAGEDATA['actionsCategories'] as $category) {
    $DBLIB->orderBy("instanceActions_id", "ASC");
    $DBLIB->orderBy("instanceActions_name", "ASC");
    $DBLIB->where("instanceActionsCategories_id", $category["instanceActionsCategories_id"]);
    $PAGEDATA['actions'][] = ["category" => $category, "actions" => $DBLIB->get("instanceActions")];
}

$DBLIB->orderBy("instancePositions_rank", "ASC");
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("instancePositions_deleted",0);
$PAGEDATA['positions'] = $DBLIB->get("instancePositions");

echo $TWIG->render('instances/instances_permissions.twig', $PAGEDATA);
?>
