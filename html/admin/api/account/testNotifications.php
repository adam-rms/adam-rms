<?php
require_once __DIR__ . '/../apiHeadSecure.php';
if ($_POST['users_userid'] != $AUTH->data["users_userid"] && $AUTH->permissionCheck(22)) $userid = $bCMS->sanitizeString($_POST['users_userid']);
else $userid = $AUTH->data["users_userid"];

$notification = notify(0,$userid, false, "Test Notification from AdamRMS", '<h1 style="margin: 0 0 10px 0; font-family: sans-serif; font-size: 25px; line-height: 30px; color: #333333; font-weight: normal;">Test Email from AdamRMS!</h1><p style="margin: 0;"><a href="' . $CONFIG['ROOTURL'] . '/">Login to the dashboard</a></p><br/><i><b>N.B.</b>You can disregard this message if you did not request it</i>');

finish($notification);

?>
