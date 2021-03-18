<?php
require_once __DIR__ . '/../../common/coreHead.php';

$DBLIB->where("instances_deleted",0);
$DBLIB->where("instances_publicConfig",NULL, "IS NOT");
$instances = $DBLIB->get("instances");
$PAGEDATA['INSTANCE'] = false;
foreach ($instances as $instance) {
    $instance['publicData'] = json_decode($instance['instances_publicConfig'],true);
    if (!is_array($instance['publicData']['customDomains'])) continue;
    foreach ($instance['publicData']['customDomains'] as $domain) {
        if (trim($_SERVER['SERVER_NAME']) == $domain) {
            $PAGEDATA['INSTANCE'] = $instance;
            break;
        }
    }
}
if (!$PAGEDATA['INSTANCE'] or !isset($PAGEDATA['INSTANCE']['publicData']['enabled']) or !$PAGEDATA['INSTANCE']['publicData']['enabled']) die("404");

$PAGEDATA['INSTANCE']['FILES'] = $bCMS->s3List(15, $PAGEDATA['INSTANCE']['instances_id']);