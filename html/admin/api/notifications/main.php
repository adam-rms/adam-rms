<?php
require_once __DIR__ . '/../apiHead.php'; //Can't be head secure as this is used by pages that aren't secure
require_once __DIR__ . '/email/email.php';
require_once __DIR__ . '/notificationTypes.php';
function notify($typeID, $userid, $instanceID, $headline, $message = false, $emailTemplate = false, $array = false) {
    global $bCMS,$NOTIFICATIONTYPES;
    foreach ($NOTIFICATIONTYPES['TYPES'] as $typeList) {
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
            }
        }
    }
    return true;
}

/** @OA\Get(
 *     path="/notifications/main.php", 
 *     summary="Notify function", 
 *     description="This function is called by the notify function in the client-side code.  
It returns a function to call rather than a response.
", 
 *     operationId="", 
 *     tags={"notifications"}, 
 *     )
 */