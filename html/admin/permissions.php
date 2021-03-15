<?php
require_once __DIR__ . '/common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Permissions", "BREADCRUMB" => false];

if (!$AUTH->permissionCheck(11)) die($TWIG->render('404.twig', $PAGEDATA));


$DBLIB->orderBy("actionsCategories_order", "ASC");
$PAGEDATA['actionsCategories'] = $DBLIB->get("actionsCategories");

$PAGEDATA['actions'] = [];
foreach ($PAGEDATA['actionsCategories'] as $category) {
    $DBLIB->orderBy("actions_id", "ASC");
    $DBLIB->orderBy("actions_name", "ASC");
    $DBLIB->where("actionsCategories_id", $category["actionsCategories_id"]);
    $PAGEDATA['actions'][] = ["category" => $category, "actions" => $DBLIB->get("actions")];
}

$positions = $DBLIB->get("positionsGroups");
$PAGEDATA['positions'] = [];
foreach ($positions as $position) {
    $PAGEDATA['positions'][$position['positionsGroups_id']] = $position;
}


$DBLIB->orderBy("positions_rank", "ASC");
$actualPositions = $DBLIB->get("positions");
$PAGEDATA['actualPositions'] = [];
foreach ($actualPositions as $position) {
    $position['positions_positionsGroups'] = explode(",", $position['positions_positionsGroups']);
    $position['group'] = [];
    foreach ($position['positions_positionsGroups'] as $group) {
        $position['group'][] = $PAGEDATA['positions'][$group];
    }
    $PAGEDATA['actualPositions'][] = $position;
}

echo $TWIG->render('permissions.twig', $PAGEDATA);
?>
