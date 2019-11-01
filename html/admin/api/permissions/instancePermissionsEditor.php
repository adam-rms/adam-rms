<?php
require_once __DIR__ . '/../apiHeadSecure.php';
header("Content-Type: text/plain");


if (!$AUTH->instancePermissionCheck(12) or !isset($_GET['position'])) die("404");

$DBLIB->where ('instancePositions_id', $bCMS->sanitizeString($_GET['position']));
$position = $DBLIB->getone("instancePositions");
$position['permissions'] = explode(",",$position['instancePositions_actions']);


if (isset($_GET['removepermission'])) {
	if(($key = array_search($_GET['removepermission'], $position['permissions'])) !== false) {
		unset($position['permissions'][$key]);
	} else die('2');
} elseif (isset($_GET['addpermission'])) {
	array_push($position['permissions'],$_GET['addpermission']);
}

$DBLIB->where ('instancePositions_id', $bCMS->sanitizeString($_GET['position']));
if ($DBLIB->update ('instancePositions', ['instancePositions_actions' => implode(",",$position['permissions'])])) {
	$bCMS->auditLog("UPDATE", "instancePositions", $bCMS->sanitizeString($_GET['position']) . " - " . implode(",",$position['permissions']), $AUTH->data['users_userid']);
	die('1');
}
else die('2');
?>
