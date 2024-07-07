<?php
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/../../common/head.php';
header('Access-Control-Allow-Origin: *');
if ($_GET['i'] == null) exit;
$DBLIB->where("instances_deleted", 0);
$DBLIB->where("instances_publicConfig", NULL, "IS NOT");
$DBLIB->where("instances_id", $_GET['i']);
$PAGEDATA['INSTANCE'] = $DBLIB->getOne("instances");
$PAGEDATA['INSTANCE']['publicData'] = json_decode($PAGEDATA['INSTANCE']['instances_publicConfig'], true);

if (!$PAGEDATA['INSTANCE'] or !isset($PAGEDATA['INSTANCE']['publicData']['enabled']) or !$PAGEDATA['INSTANCE']['publicData']['enabled']) die("Disabled by AdamRMS administrator");
