<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['widgetName'])) finish(false, ["code" => null, "message"=> "Attribute error"]);
$widgetName = $bCMS->sanitizeString($_POST['widgetName']);
$currentWidgets = explode(",",$AUTH->data['users_widgets']);
$key = array_search($widgetName, $currentWidgets);
if ($key !== false) {
    unset($currentWidgets[$key]);
} else {
    array_push($currentWidgets, $widgetName);
}
$DBLIB->where ('users_userid', $AUTH->data['users_userid']);
if ($DBLIB->update ('users', ["users_widgets" => implode(",", $currentWidgets)])) finish(true);
else finish(false, ["code" => null, "message"=> "Update error"]);

?>
