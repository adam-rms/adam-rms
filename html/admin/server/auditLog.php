<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Audit Log", "BREADCRUMB" => false];

if (!$AUTH->serverPermissionCheck("VIEW-AUDIT-LOG")) die($TWIG->render('404.twig', $PAGEDATA));

if (isset($_GET['q'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_GET['q']);
else $PAGEDATA['search'] = null;

if (isset($_GET['page'])) $page = $bCMS->sanitizeString($_GET['page']);
else $page = 1;
$DBLIB->pageLimit = 50;

if (strlen($PAGEDATA['search']) > 0) {
	//Search
	$DBLIB->where("
		(auditLog.auditLog_actionType LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%'
		OR auditLog.auditLog_actionTable LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%'
		OR auditLog.auditLog_actionData LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%')
    ");
}
$DBLIB->orderBy("auditLog_timestamp", "DESC");
if (isset($_GET['userby'])) {
	$DBLIB->where("auditLog.users_userid", $bCMS->sanitizeString($_GET['userby']));
	$PAGEDATA['pageConfig']['userby'] = true;
}
if (isset($_GET['userto'])) {
	$DBLIB->where("auditLog.auditLog_actionUserid", $bCMS->sanitizeString($_GET['userto']));
	$PAGEDATA['pageConfig']['userto'] = true;
}
$DBLIB->join("(users userSource)", "auditLog.users_userid=userSource.users_userid", "LEFT");
$DBLIB->join("(users userAction)", "auditLog.auditLog_actionUserid=userAction.users_userid", "LEFT");
$PAGEDATA['items'] = [];
$items = $DBLIB->get("auditLog", [$DBLIB->pageLimit*($page-1),$DBLIB->pageLimit*$page], ["userSource.users_name1", "userSource.users_name2", "userSource.users_userid", "auditLog.*","userAction.users_name1 AS userAction_name1", "userAction.users_name2 AS userAction_name2", "userAction.users_userid AS userAction_userid"]);
$PAGEDATA['pagination'] = ["page" => $page, "total" => $DBLIB->getValue("auditLog", "COUNT(auditLog_id)")];
foreach ($items as $item) {
	$json = json_decode($item['auditLog_actionData'],true);
	if (json_last_error() === JSON_ERROR_NONE) { //Check if it's valid json
		$item['auditLog_actionData'] = $json;
	}
	$PAGEDATA['items'][] = $item;
}


echo $TWIG->render('server/auditLog.twig', $PAGEDATA);
?>
