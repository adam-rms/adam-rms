<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck(125)) die($TWIG->render('404.twig', $PAGEDATA));

if (!isset($_GET['p']) or strlen($_GET['p']) < 1) die($TWIG->render('404.twig', $PAGEDATA));

//get all views for page
$DBLIB->where("cmsPages_id",$_GET['p']);
$views = $DBLIB->get("cmspagesviews");

//declare stats array
$PAGEDATA['stats'] = [
    'today'=>0,
    'week'=>0,
];

foreach ($views as $key => $view){
    $date = new DateTime($view['cmsPagesViews_timestamp']);
    $date = $date->format('Y-m-d');
    if ($date == date("Y-m-d")){
        $PAGEDATA['stats']['today'] ++;
        $PAGEDATA['stats']['week'] ++;
    } elseif ($date > date('Y-m-d', strtotime("-7 days"))){
        $PAGEDATA['stats']['week'] ++;
    }

}

print_r($PAGEDATA['stats']);

echo $TWIG->render('cms/cms_stats.twig', $PAGEDATA);
?>
