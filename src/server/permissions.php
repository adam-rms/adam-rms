<?php
require_once __DIR__ . '/../common/headSecure.php';
require_once __DIR__ . '/../common/libs/Auth/serverActions.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Permissions", "BREADCRUMB" => false];

if (!$AUTH->serverPermissionCheck("PERMISSIONS:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

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

$PAGEDATA['actions'] = $serverActions;

echo $TWIG->render('server/permissions.twig', $PAGEDATA);
?>
