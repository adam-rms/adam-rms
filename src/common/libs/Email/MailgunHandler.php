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
    } elseif ($CONFIGCLASS->get('EMAILS_FROMDOMAIN') == false) {
      trigger_error("Email Provider is Mailgun, but From Domain not set", E_USER_WARNING);
      return true;
    }

    $mgServer = ($CONFIGCLASS->get('EMAILS_PROVIDERS_MAILGUN_LOCATION')) == "EU" ? "https://api.eu.mailgun.net" : "https://api.mailgun.net";
    $mgClient = \Mailgun\Mailgun::create($CONFIGCLASS->get('EMAILS_PROVIDERS_MAILGUN_APIKEY'), $mgServer);
    $domain = $CONFIGCLASS->get('EMAILS_FROMDOMAIN');
    $userName = $bCMS->cleanString($user["userData"]["users_name1"] .  ' ' . $user["userData"]["users_name2"]);
    $params = array(
      "html" => $body,
      "from" => $CONFIG['PROJECT_NAME'] . " <" . $CONFIGCLASS->get('EMAILS_FROMEMAIL') . ">",
      "to" => $userName . " <" . $user["userData"]["users_email"] . ">",
      "subject" => $bCMS->cleanString($subject),
    );

    $response = $mgClient->messages()->send($domain, $params);

    if ($response->getStatusCode() == 200) {
      return parent::logEmail($user, $subject, $body);
    }
    return false;
  }
}
