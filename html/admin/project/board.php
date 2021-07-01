<?php
ini_set('memory_limit','2048M');
require_once __DIR__ . '/../common/headSecure.php';
if (!$AUTH->instancePermissionCheck(20) || !isset($_GET['id'])) {die($TWIG->render('404.twig', $PAGEDATA));}

require_once __DIR__ . '/../api/projects/data.php'; //Where most of the data comes from

if (count($PAGEDATA['FINANCIALS']['assetsAssigned']) == 0) {die($TWIG->render('404.twig', $PAGEDATA));}

foreach ($PAGEDATA['assetsAssignmentsStatus'] as $status) {
    $assets=[];
    foreach ($PAGEDATA['FINANCIALS']['assetsAssigned'] as $asset){
        foreach ($asset['assets'] as $assetSUB){
            if ($assetSUB['assetsAssignmentsStatus_name'] == $status['assetsAssignmentsStatus_name']){
                array_push($assets, $assetSUB);
            }
        }
    }
    $PAGEDATA['assetsAssignmentsStatus'][$status['assetsAssignmentsStatus_order'] - 1]['assets'] = $assets;
}

foreach ($PAGEDATA['FINANCIALS']['assetsAssignedSUB'] as $instance){
    $DBLIB->orderBy("assetsAssignmentsStatus_order","ASC");
    $DBLIB->where("assetsAssignmentsStatus.instances_id", $instance['instance']['instances_id']);
    $PAGEDATA['FINANCIALS']['assetsAssignedSUB'][$instance['instance']['instances_id']]['statuses'] = $DBLIB->get("assetsAssignmentsStatus");
    foreach ($PAGEDATA['FINANCIALS']['assetsAssignedSUB'][$instance['instance']['instances_id']]['statuses'] as $status){
        $assets=[];
        foreach ($instance['assets'][$instance['instance']['instances_id'] + 1]['assets'] as $asset){
            //if asset status is null, add to the first column
            if ($asset['assetsAssignmentsStatus_name'] == null && $status['assetsAssignmentsStatus_order'] == 1){
                array_push($assets, $asset);
            }
            if ($asset['assetsAssignmentsStatus_name'] == $status['assetsAssignmentsStatus_name']){
                array_push($assets, $asset);
            }
        }
        $PAGEDATA['FINANCIALS']['assetsAssignedSUB'][$instance['instance']['instances_id']]['statuses'][$status['assetsAssignmentsStatus_order'] - 1]['assets'] = $assets;
    }
}

echo $TWIG->render('project/project_asset_board.twig', $PAGEDATA);