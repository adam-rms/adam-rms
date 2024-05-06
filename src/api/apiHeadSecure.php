<?php
require_once __DIR__ . '/apiHead.php';

if (!$GLOBALS['AUTH']->login) {
    if ($CONFIG['DEV']) finish(false,["message"=>"AUTH FAIL - " . $GLOBALS['AUTH']->debug]);
    else finish(false,["code" => "AUTH", "message"=>"AUTH FAIL"]);
}
if (!$CONFIG['DEV']) {
    Sentry\configureScope(function (Sentry\State\Scope $scope): void {
        $scope->setUser(['username' => $GLOBALS['AUTH']->data['users_username'],"id"=> $GLOBALS['AUTH']->data['users_userid']]);
        if ($GLOBALS['AUTH']->data['instance']) $scope->setExtra('instances_id', $AUTH->data['instance']['instances_id']);
    });
} elseif (!$AUTH->serverPermissionCheck("USE-DEV") and !$GLOBALS['AUTH']->data['viewSiteAs']) finish(false,["message"=>"Can't use dev site"]);

$PAGEDATA['AUTH'] = $GLOBALS['AUTH'];
$PAGEDATA['USERDATA'] = $GLOBALS['AUTH']->data;
$PAGEDATA['USERDATA']['users_email_md5'] = md5($PAGEDATA['USERDATA']['users_email']);


$DBLIB->insert("analyticsEvents", [
    "analyticsEvents_timestamp" => date ("Y-m-d H:i:s"),
    "users_userid" => $AUTH->data['users_userid'],
    "adminUser_users_userid" => $AUTH->data['viewSiteAs'] ? $AUTH->data['viewSiteAs']['users_userid'] : null,
    "authTokens_id" => $AUTH->data['authTokens_id'],
    "instances_id" => $AUTH->data['instance'] ?  $AUTH->data['instance']['instances_id'] : null,
    "analyticsEvents_path" => strtok($_SERVER["REQUEST_URI"], '?'),
    "analyticsEvents_action" => "API-CALL",
    "analyticsEvents_payload" =>  strlen(json_encode($_POST)) > 65535 ? null : json_encode($_POST),
]);