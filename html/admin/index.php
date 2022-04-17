<?php
require_once __DIR__ . '/common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Dashboard", "BREADCRUMB" => false];

if (isset($_GET['i'])) {
    if ($AUTH->permissionCheck(21)) {
        $_SESSION['instanceID'] = $_GET['i']; //It doesn't even bother to verify the instance ID as the user is trusted to be BStudios Staff
        header("Location: " . $CONFIG['ROOTURL'] . "?");
    } else {
        $GLOBALS['AUTH']->setInstance($_GET['i']);
        header("Location: " . $CONFIG['ROOTURL'] . "?");
    }
}
if ($AUTH->permissionCheck(18) and isset($_GET['phpversion'])) {
    phpinfo();
    exit;
}


if ($AUTH->data['instance']["instancePositions_id"] && $AUTH->data['instance']["cmsPages_id"] != null) {
    $DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
    $DBLIB->where("cmsPages_deleted",0);
    $DBLIB->where("cmsPages_archived",0);
    $DBLIB->where("(cmsPages_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", cmsPages_visibleToGroups) > 0))");
    $DBLIB->where("cmsPages_id",$AUTH->data['instance']["cmsPages_id"]);
    $PAGEDATA['PAGE'] = $DBLIB->getOne("cmsPages");
    if ($PAGEDATA['PAGE']) {
        $DBLIB->where("cmsPages_id",$PAGEDATA['PAGE']['cmsPages_id']);
        $DBLIB->orderBy("cmsPagesDrafts_timestamp","DESC");
        if (isset($_GET['r']) and $AUTH->instancePermissionCheck(126)) {
            $DBLIB->where("cmsPagesDrafts_id",$_GET['r']);
            $PAGEDATA['specificRevision'] = true;
        }
        $PAGEDATA['PAGE']['DRAFTS'] = $DBLIB->getOne("cmsPagesDrafts",["cmsPagesDrafts_id","cmsPagesDrafts_data","cmsPagesDrafts_revisionID"]);
        if ($PAGEDATA['PAGE']['DRAFTS']) $PAGEDATA['PAGE']['DRAFTS']['cmsPagesDrafts_dataARRAY'] = json_decode($PAGEDATA['PAGE']['DRAFTS']['cmsPagesDrafts_data'],true);

        $DBLIB->insert("cmsPagesViews",[
            "cmsPages_id" => $PAGEDATA['PAGE']['cmsPages_id'],
            "cmsPagesViews_timestamp" => date("Y-m-d H:i:s"),
            "users_userid" => $AUTH->data['users_userid'],
            "cmsPages_type" => 3
        ]);
        die($TWIG->render('dashboard-cmsPage.twig', $PAGEDATA));
    }
}

/**
 * Default dashboard, this is not executed if a CMS_PAGE is found above
 */

$DBLIB->orderBy("authTokens_created","DESC");
$DBLIB->where("users_userid",$AUTH->data['users_userid']);
$DBLIB->where("(authTokens_deviceType != 'Web')");
$PAGEDATA['APPLOGIN'] = $DBLIB->getone("authTokens",["authTokens_deviceType"]);


$PAGEDATA['WIDGETS'] = new statsWidgets(explode(",",$AUTH->data['users_widgets']),false);

echo $TWIG->render('dashboard.twig', $PAGEDATA);
?>
