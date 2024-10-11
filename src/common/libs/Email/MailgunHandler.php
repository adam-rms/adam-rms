<?php
require __dir__ . '/EmailHandler.php';

/**
 * Email handler for Mailgun email service
 */
class MailgunEmailHandler extends EmailHandler
{
  public static function sendEmail($user, $subject, $body): bool
  {
    global $CONFIG, $bCMS, $CONFIGCLASS;

    if ($CONFIGCLASS->get('EMAILS_PROVIDERS_MAILGUN_APIKEY') == false) {
      trigger_error("Email Provider is Mailgun, but Mailgun API Key not set", E_USER_WARNING);
      return true;
    }

    $emailFromDomain = substr($CONFIGCLASS->get('EMAILS_FROMEMAIL'), strpos($CONFIGCLASS->get('EMAILS_FROMEMAIL'), "@") + 1);

    $mgServer = ($CONFIGCLASS->get('EMAILS_PROVIDERS_MAILGUN_LOCATION')) == "EU" ? "https://api.eu.mailgun.net" : "https://api.mailgun.net";
    $mgClient = \Mailgun\Mailgun::create($CONFIGCLASS->get('EMAILS_PROVIDERS_MAILGUN_APIKEY'), $mgServer);
    $userName = $bCMS->cleanString($user["userData"]["users_name1"] .  ' ' . $user["userData"]["users_name2"]);
    $params = array(
      "html" => $body,
      "from" => $CONFIG['PROJECT_NAME'] . " <" . $CONFIGCLASS->get('EMAILS_FROMEMAIL') . ">",
      "to" => $userName . " <" . $user["userData"]["users_email"] . ">",
      "subject" => $bCMS->cleanString($subject),
    );

    $response = $mgClient->messages()->send($emailFromDomain, $params);

    if ($response->getStatusCode() == 200) {
      return parent::logEmail($user, $subject, $body);
    }
    return false;
  }
}
