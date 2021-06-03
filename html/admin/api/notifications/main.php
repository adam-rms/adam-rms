<?php
require_once __DIR__ . '/../apiHead.php'; //Can't be head secure as this is used by pages that aren't secure
require_once __DIR__ . '/email/email.php';
require_once __DIR__ . '/slack/slack.php';
function notify($typeID, $userid, $instanceID, $headline, $message = false, $emailTemplate = false, $array = false) {
    global $CONFIG,$bCMS;
    foreach ($CONFIG['NOTIFICATIONS']['TYPES'] as $typeList) {
        if ($typeList['id'] == $typeID) $type = $typeList;
    }
    if (!isset($type)) return false;
    $user = $bCMS->notificationSettings($userid);
    $userConfigThis = $user['settings'][$type['id']]['methodsUser'];
    if (!$user or !$userConfigThis) return false;
    foreach ($userConfigThis as $methodid=>$setting) {
        //Send a notification
        if ($setting) {
            switch ($methodid) {
                case 0:
                    //POST
                    break;
                case 1:
                    sendEmail($user, $instanceID, $headline, $message, $emailTemplate, $array);
                    break;
                case 2:
                    //SMS
                    break;
                case 3:
                    //Mobile Push
                    break;
                case 4:
                    //Slack
                    sendSlackNotification($user, $headline);
                    break;
            }
        }
    }
    return true;
}