<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("CMS:CMS_PAGES:EDIT")) die("404");

foreach ($_POST['order'] as $count=>$item) {
    if ($item == "") continue;
    $DBLIB->where("cmsPages_deleted", 0);
    $DBLIB->where("cmsPages_id",$item);
    $DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
    if (!$DBLIB->update("cmsPages", ["cmsPages_navOrder" => $count], 1)) finish(false);
}
$bCMS->auditLog("RANK-CMSPAGES", "cmsPages", "Set the order of pages", $AUTH->data['users_userid']);
finish(true);
