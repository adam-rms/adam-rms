<?php
require_once __DIR__ . '/../../apiHead.php';
function sendEmail($user, $instanceID, $subject, $html = false, $template = false, $emailData = false) {
    global $DBLIB, $CONFIG, $TWIG, $bCMS, $CONFIGCLASS;
	if (!$user or $user["userData"]["users_email"] == '') return false; //If the user hasn't entered an E-Mail address yet

    if ($CONFIGCLASS->get('EMAILS_ENABLED') !== "Enabled") {
        return true;
    }

    if ($instanceID) {
        $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id", "LEFT");
        $DBLIB->join("instances", "instancePositions.instances_id=instances.instances_id", "LEFT");
        $DBLIB->where("users_userid", $user["userData"]['users_userid']);
        $DBLIB->where("instances.instances_id", $instanceID);
        $DBLIB->where("userInstances_deleted", 0);
        $DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
        $DBLIB->where("instances.instances_deleted", 0);
        $instance = $DBLIB->getone("userInstances", ["instances.instances_name", "instances.instances_address", "instances.instances_emailHeader"]);
    } else $instance = false;

    $outputHTML = $TWIG->render('api/notifications/email/email_template.twig', ["SUBJECT" => $subject, "HTML"=> $bCMS->cleanString($html), "CONFIG" => $CONFIG, "DATA" => $emailData, "TEMPLATE" => $template, "INSTANCE" => $instance]); // Subject is escaped by twig, but the HTML is not.

    switch ($CONFIGCLASS->get('EMAILS_PROVIDER')) {
        case 'Sendgrid':
            require __DIR__ . '/../../../common/libs/Email/SendgridHandler.php';
            return SendgridEmailHandler::sendEmail($user, $subject, $outputHTML);
        case 'Mailgun':
            require __DIR__ . '/../../../common/libs/Email/MailgunHandler.php';
            return MailgunEmailHandler::sendEmail($user, $subject, $outputHTML);
        case 'Postmark':
            require __DIR__ . '/../../../common/libs/Email/PostmarkHandler.php';
            return PostmarkEmailHandler::sendEmail($user, $subject, $outputHTML);
        case 'SMTP':
            require __DIR__ . '/../../../common/libs/Email/SMTPHandler.php';
            return SMTPEmailHandler::sendEmail($user, $subject, $outputHTML);
        default:
            trigger_error("Unknown email provider set", E_USER_ERROR);
            return false;
    }
}

/** @OA\Get(
 *     path="/notifications/email/email.php", 
 *     summary="Email Notifications", 
 *     description="Send an email to the user. This returns a function to call rather than a response.", 
 *     operationId="emailNotifications", 
 *     tags={"notifications"}, 
 *     )
 */