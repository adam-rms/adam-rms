<?php
require_once __DIR__ . '/apiHead.php';

if (!$GLOBALS['AUTH']->login) {
    if ($CONFIG['DEV']) finish(false,["message"=>"AUTH FAIL - " . $GLOBALS['AUTH']->debug]);
    else finish(false,["message"=>"AUTH FAIL"]);
}
if (!$CONFIG['DEV']) {
    Sentry\configureScope(function (Sentry\State\Scope $scope): void {
        $scope->setUser(['username' => $GLOBALS['AUTH']->data['users_username'],"id"=> $GLOBALS['AUTH']->data['users_userid']]);
        if ($GLOBALS['AUTH']->data['instance']) $scope->setExtra('instances_id', $AUTH->data['instance']['instances_id']);
    });
} elseif (!$AUTH->permissionCheck(17) and !$GLOBALS['AUTH']->data['viewSiteAs']) finish(false,["message"=>"Can't use dev site"]);

$PAGEDATA['USERDATA'] = $GLOBALS['AUTH']->data;
$PAGEDATA['USERDATA']['users_email_md5'] = md5($PAGEDATA['USERDATA']['users_email']);