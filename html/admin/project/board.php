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
    $PAGEDATA['assetsAssignmentsStatus'][$status['assetsAssignmentsStatus_id'] - 1]['assets'] = $assets;
}

echo $TWIG->render('project/project_asset_board.twig', $PAGEDATA);