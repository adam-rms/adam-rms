<?php
require_once __DIR__ . '/../common/headSecure.php';
require_once __DIR__ . '/../common/libs/Auth/instanceActions.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Permissions", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck("BUSINESS:ROLES_AND_PERMISSIONS:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));


$DBLIB->orderBy("instancePositions_rank", "ASC");
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("instancePositions_deleted",0);
$PAGEDATA['positions'] = $DBLIB->get("instancePositions");

$PAGEDATA['actions'] = $instanceActions;
foreach ($PAGEDATA['actions'] as $key=>$action) {
  $PAGEDATA['actions'][$key]['key'] = $key;
}
usort($PAGEDATA['actions'], function($a, $b) { 
  return $a['Category'] <=> $b['Category'] ?: $a['Table'] <=> $b['Table'] ?: $a['Type'] <=> $b['Type'] ?: $a['Detail'] <=> $b['Detail'];
});
$PAGEDATA['actionTypeCounts'] = [];
foreach ($PAGEDATA['actions'] as $action) {
  $PAGEDATA['actionTypeCounts'][$action['Category']]['Count'] += 1;
  $PAGEDATA['actionTypeCounts'][$action['Category']]['Tables'][$action['Table']]['Count'] += 1;
  $PAGEDATA['actionTypeCounts'][$action['Category']]['Tables'][$action['Table']]['Types'][$action['Type']] += 1;
}

echo $TWIG->render('instances/instances_permissions.twig', $PAGEDATA);
?>
