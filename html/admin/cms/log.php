<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck(128)) die($TWIG->render('404.twig', $PAGEDATA));

if (!isset($_GET['p']) or strlen($_GET['p']) < 1) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['cmsPages_id'] = $_GET['p'];
$DBLIB->orderBy("cmsPagesViews_timestamp","ASC");
$DBLIB->join("users","cmsPagesViews.users_userid=users.users_userid","LEFT");
$DBLIB->where("cmsPages_id",$_GET['p']);
$PAGEDATA['VIEWERS'] = $DBLIB->get("cmsPagesViews",null,["cmsPagesViews.*","users.users_name1","users.users_name2","users.users_userid"]);

echo $TWIG->render('cms/cms_log.twig', $PAGEDATA);
?>
