<?php
require_once __DIR__ . '/../../apiHead.php';
function sendEmail($userid, $instanceID = false, $subject, $html = false, $template = false, $array = false) {
	global $DBLIB, $CONFIG;
	if (!$userid) return false;
	$DBLIB->where('users_userid', $userid);
	$user = $DBLIB->getone('users', ["users_email", "users_userid", "users_name1", "users_name2"]);
	if (!$user or $user["users_email"] == '') return false; //If the user hasn't entered an E-Mail address yet

    if ($instanceID) {
        $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id", "LEFT");
        $DBLIB->join("instances", "instancePositions.instances_id=instances.instances_id", "LEFT");
        $DBLIB->where("users_userid", $user['users_userid']);
        $DBLIB->where("instances.instances_id", $instanceID);
        $DBLIB->where("userInstances_deleted", 0);
        $DBLIB->where("instances.instances_deleted", 0);
        $instance = $DBLIB->getone("userInstances", ["instances.instances_name", "instances.instances_address", "instances.instances_emailHeader"]);
        if ($instance['instances_emailHeader'] != null) $instance['instances_emailHeader'] = $bCMS->s3URL($instance['instances_emailHeader'],"medium");
    } else $instance = false;

    $outputHTML = $TWIG->render('api/notifications/email/email_template.twig', ["SUBJECT" => $subject, "HTML"=> $html, "CONFIG" => $CONFIG, "DATA" => $array, "TEMPLATE" => $template, "INSTANCE" => $instance]);

    $email = new \SendGrid\Mail\Mail();
    $email->setFrom($CONFIG['PROJECT_FROM_EMAIL'], $CONFIG['PROJECT_NAME']);
    $email->setSubject($subject);
    $email->addTo($user["users_email"], $user["users_name1"] .  ' ' . $user["users_name2"]);
    //$email->addContent("text/plain", "and easy to do anywhere, even with PHP");
    $email->addContent("text/html", $outputHTML);
    $sendgrid = new \SendGrid($CONFIG['SENDGRID']['APIKEY']);
    $response = $sendgrid->send($email);
    if ($response->statusCode() == 202) {
        $sqldata = Array ("users_userid" => $userid,
            "emailSent_html" => $outputHTML,
            "emailSent_subject" => $subject,
            "emailSent_sent" => date('Y-m-d G:i:s'),
            "emailSent_fromEmail" => $CONFIG['PROJECT_FROM_EMAIL'],
            "emailSent_fromName" => $CONFIG['PROJECT_NAME'],
            'emailSent_toEmail' => $user["users_email"],
            'emailSent_toName' => $user["users_name1"] .  ' ' . $user["users_name2"]
        );
        $emailid = $DBLIB->insert('emailSent', $sqldata);
        if(!$emailid) die('Sorry - Could not send E-Mail');
        else return true;
    } else die('Sorry - Could not send E-Mail');
}
