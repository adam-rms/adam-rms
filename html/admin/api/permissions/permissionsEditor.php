<?php
require_once __DIR__ . '/../apiHeadSecure.php';
header("Content-Type: text/plain");


if (!$AUTH->serverPermissionCheck("PERMISSIONS:EDIT") or !isset($_POST['position'])) die("404");

$DBLIB->where ('positionsGroups_id', $bCMS->sanitizeString($_POST['position']));
$position = $DBLIB->getone("positionsGroups");
$position['permissions'] = explode(",",$position['positionsGroups_actions']);


if (isset($_POST['removepermission'])) {
	if(($key = array_search($_POST['removepermission'], $position['permissions'])) !== false) {
		unset($position['permissions'][$key]);
	} else die('2');
} elseif (isset($_POST['addpermission'])) {
	array_push($position['permissions'],$_POST['addpermission']);
}
asort($position['permissions']); //Prevents it being associative when downloaded
$DBLIB->where ('positionsGroups_id', $bCMS->sanitizeString($_POST['position']));
if ($DBLIB->update ('positionsGroups', ['positionsGroups_actions' => implode(",",$position['permissions'])])) {
	$bCMS->auditLog("UPDATE", "positionsGroups", $bCMS->sanitizeString($_POST['position']) . " - " . implode(",",$position['permissions']), $AUTH->data['users_userid']);
	die('1');
}
else die('2');
?>
