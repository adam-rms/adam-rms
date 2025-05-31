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

    $outputHTML = $TWIG->render('api/notifications/email/email_template.twig', ["SUBJECT" => $subject, "HTML"=> $bCMS->cleanString($html), "CONFIG" => $CONFIG, "DATA" => $emailData, "TEMPLATE" => $template, "INSTANCE" => $instance, "FOOTER" => $CONFIGCLASS->get('EMAILS_FOOTER')]); // Subject is escaped by twig, but the HTML is not.

    switch ($CONFIGCLASS->get('EMAILS_PROVIDER')) {
        case 'Sendgrid':
            require_once __DIR__ . '/../../../common/libs/Email/SendgridHandler.php';
            return SendgridEmailHandler::sendEmail($user, $subject, $outputHTML);
        case 'Mailgun':
            require_once __DIR__ . '/../../../common/libs/Email/MailgunHandler.php';
            return MailgunEmailHandler::sendEmail($user, $subject, $outputHTML);
        case 'Postmark':
            require_once __DIR__ . '/../../../common/libs/Email/PostmarkHandler.php';
            return PostmarkEmailHandler::sendEmail($user, $subject, $outputHTML);
        case 'SMTP':
            require_once __DIR__ . '/../../../common/libs/Email/SMTPHandler.php';
            return SMTPEmailHandler::sendEmail($user, $subject, $outputHTML);
        case 'AWS SES':
            require_once __DIR__ . '/../../../common/libs/Email/AWSSESHandler.php';
            // Fetch AWS SES specific configuration
            $awsConfig = [
                'aws_access_key_id' => $CONFIGCLASS->get('EMAILS_PROVIDERS_AWSSES_KEY'),
                'aws_secret_access_key' => $CONFIGCLASS->get('EMAILS_PROVIDERS_AWSSES_SECRET'),
                'aws_region' => $CONFIGCLASS->get('EMAILS_PROVIDERS_AWSSES_REGION'),
            ];
            // Ensure all AWS config values are present
            if (empty($awsConfig['aws_access_key_id']) || empty($awsConfig['aws_secret_access_key']) || empty($awsConfig['aws_region'])) {
                trigger_error("AWS SES configuration is incomplete. Please check key, secret, and region settings.", E_USER_WARNING);
                return false;
            }

            $fromEmail = $CONFIGCLASS->get('EMAILS_FROMEMAIL');
            if (empty($fromEmail)) {
                trigger_error("Sender email (EMAILS_FROMEMAIL) is not configured.", E_USER_WARNING);
                return false;
            }

            try {
                // Use the fully qualified namespace
                $awsSesHandler = new \Common\Libs\Email\AWSSESEmailHandler($awsConfig);
                return $awsSesHandler->sendEmail(
                    $user["userData"]["users_email"],
                    $subject,
                    $outputHTML,
                    $fromEmail
                );
            } catch (\InvalidArgumentException $e) {
                trigger_error("Error initializing AWSSESEmailHandler: " . $e->getMessage(), E_USER_WARNING);
                return false;
            } catch (\Exception $e) {
                trigger_error("Error sending email with AWS SES: " . $e->getMessage(), E_USER_WARNING);
                return false;
            }
        default:
            trigger_error("Unknown email provider set: " . $CONFIGCLASS->get('EMAILS_PROVIDER'), E_USER_ERROR);
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