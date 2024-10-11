<?php
require __dir__ . '/EmailHandler.php';

/**
 * Email handler for Sendgrid email service
 */
class SendgridEmailHandler extends EmailHandler
{
  public static function sendEmail($user, $subject, $body): bool
  {
    global $CONFIG, $bCMS, $CONFIGCLASS;

    if ($CONFIGCLASS->get('EMAILS_PROVIDERS_APIKEY') == false) {
      trigger_error("Email Provider is Sendgrid, but API Key not set", E_USER_WARNING);
      return true;
    }

    $email = new \SendGrid\Mail\Mail();
    $email->setFrom($CONFIGCLASS->get('EMAILS_FROMEMAIL'), $CONFIG['PROJECT_NAME']);
    $email->setSubject($bCMS->cleanString($subject));  //Subject should be escaped
    $email->addTo($user["userData"]["users_email"], $user["userData"]["users_name1"] .  ' ' . $user["userData"]["users_name2"]);
    $email->addContent("text/html", $body);
    $sendgrid = new \SendGrid($CONFIGCLASS->get('EMAILS_PROVIDERS_APIKEY'));
    $response = $sendgrid->send($email);
    if ($response->statusCode() == 202) {
      return parent::logEmail($user, $subject, $body);
    }
    return false;
  }
}
