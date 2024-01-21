<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['p']) or strlen($_POST['p']) < 1) finish(false, ["code" => "ARG_MISSING", "message" => "Missing argument 'p'"]);

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("cmsPages_deleted", 0);
$DBLIB->where("cmsPages_archived", 0);
if ($AUTH->data['instance']["instancePositions_id"]) $DBLIB->where("(cmsPages_visibleToGroups IS NULL OR (FIND_IN_SET(" . $AUTH->data['instance']["instancePositions_id"] . ", cmsPages_visibleToGroups) > 0))");
$DBLIB->where("cmsPages_id", $_POST['p']);
$PAGE = $DBLIB->getOne("cmsPages");
if (!$PAGE) finish(false, ["code" => "NO_PAGE", "message" => "Page not found"]);

$DBLIB->where("cmsPages_id", $PAGE['cmsPages_id']);
$DBLIB->orderBy("cmsPagesDrafts_timestamp", "DESC");
$PAGE['DRAFTS'] = $DBLIB->getOne("cmsPagesDrafts", ["cmsPagesDrafts_id", "cmsPagesDrafts_data", "cmsPagesDrafts_revisionID"]);
if ($PAGE['DRAFTS']) {
    $PAGE['DRAFTS']['cmsPagesDrafts_dataARRAY'] = json_decode($PAGE['DRAFTS']['cmsPagesDrafts_data'], true);
} else {
    //we have no current drafts
    $PAGE['DRAFTS']['cmsPagesDrafts_dataARRAY'] = [];
}

$DBLIB->insert("cmsPagesViews", [
    "cmsPages_id" => $PAGE['cmsPages_id'],
    "cmsPagesViews_timestamp" => date("Y-m-d H:i:s"),
    "users_userid" => $AUTH->data['users_userid'],
    "cmsPages_type" => 2
]);
$PAGE['CONTENT'] = $TWIG->render('assets/templates/cmsPage.twig', ['pageData' => $PAGE]);
finish(true, null, $PAGE);
