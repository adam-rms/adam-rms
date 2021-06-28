<?php
ini_set('memory_limit','2048M');
require_once __DIR__ . '/../common/headSecure.php';
if (!$AUTH->instancePermissionCheck(20) or !isset($_GET['id'])) die($TWIG->render('404.twig', $PAGEDATA));

require_once __DIR__ . '/../api/projects/data.php'; //Where most of the data comes from

foreach ($PAGEDATA['assetsAssignmentsStatus'] as $status) {
    $assets=[];
    foreach ($PAGEDATA['FINANCIALS']['assetsAssigned'][1]['assets'] as $asset){
        if ($asset['assetsAssignmentsStatus_name'] == $status['assetsAssignmentsStatus_name']){
            array_push($assets, $asset);
        }
    }
    $PAGEDATA['assetsAssignmentsStatus'][$status['assetsAssignmentsStatus_id'] - 1]['assets'] = $assets;
}

echo $TWIG->render('project/project_asset_board.twig', $PAGEDATA);