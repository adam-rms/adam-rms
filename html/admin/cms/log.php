<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck(125)) die($TWIG->render('404.twig', $PAGEDATA));

if (!isset($_GET['p']) or strlen($_GET['p']) < 1) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->where("cmsPages_id",$_GET['p']);

$PAGEDATA['VIEWERS'] = $DBLIB->get("cmspagesviews");

foreach ($PAGEDATA['VIEWERS'] as $key => $view){
    $DBLIB->where("users_userid", $view['users_userid']);

    $PAGEDATA['VIEWERS'][$key]['user'] = $DBLIB->getOne("users", "users_name1, users_name2");
}
//print_r($PAGEDATA['VIEWERS']);
echo $TWIG->render('cms/cms_log.twig', $PAGEDATA);
?>
