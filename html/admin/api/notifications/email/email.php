<?php
require_once __DIR__ . '/../../apiHead.php';
function sendEmail($user, $instanceID, $subject, $html = false, $template = false, $emailData = false) {
	global $DBLIB, $CONFIG,$TWIG,$bCMS;
	if (!$user or $user["userData"]["users_email"] == '') return false; //If the user hasn't entered an E-Mail address yet

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

    $outputHTML = $TWIG->render('api/notifications/email/email_template.twig', ["SUBJECT" => $subject, "HTML"=> $html, "CONFIG" => $CONFIG, "DATA" => $emailData, "TEMPLATE" => $template, "INSTANCE" => $instance]);

    $email = new \SendGrid\Mail\Mail();
    $email->setFrom($CONFIG['PROJECT_FROM_EMAIL'], $CONFIG['PROJECT_NAME']);
    $email->setSubject($subject);
    $email->addTo($user["userData"]["users_email"], $user["userData"]["users_name1"] .  ' ' . $user["userData"]["users_name2"]);
    //$email->addContent("text/plain", "and easy to do anywhere, even with PHP");
    $email->addContent("text/html", $outputHTML);
    $sendgrid = new \SendGrid($CONFIG['SENDGRID']['APIKEY']);
    $response = $sendgrid->send($email);
    if ($response->statusCode() == 202) {
        $sqldata = Array ("users_userid" => $user['userData']['users_userid'],
            "emailSent_html" => $outputHTML,
            "emailSent_subject" => $subject,
            "emailSent_sent" => date('Y-m-d G:i:s'),
            "emailSent_fromEmail" => $CONFIG['PROJECT_FROM_EMAIL'],
            "emailSent_fromName" => $CONFIG['PROJECT_NAME'],
            'emailSent_toEmail' => $user["userData"]["users_email"],
            'emailSent_toName' => $user["userData"]["users_name1"] .  ' ' . $user["userData"]["users_name2"]
        );
        $emailid = $DBLIB->insert('emailSent', $sqldata);
        if(!$emailid) die('Sorry - Could not send E-Mail');
        else return true;
    } else die('Sorry - Could not send E-Mail');
}
