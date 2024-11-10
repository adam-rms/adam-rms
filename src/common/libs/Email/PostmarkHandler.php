<?php
require_once __DIR__ . '/EmailHandler.php';

/**
 * Email handler for Postmark email service
 */
class PostmarkEmailHandler extends EmailHandler
{
  public static function sendEmail($user, $subject, $body): bool
  {
    global $CONFIG, $bCMS, $CONFIGCLASS;

    if ($CONFIGCLASS->get('EMAILS_PROVIDERS_APIKEY') == false) {
      trigger_error("Email Provider is Postmark, but API Key not set", E_USER_WARNING);
      return true;
    }

    try {

      $client = new \Postmark\PostmarkClient($CONFIGCLASS->get('EMAILS_PROVIDERS_APIKEY'));

      $from_email = $CONFIG['PROJECT_NAME'] . " <" . $CONFIGCLASS->get('EMAILS_FROMEMAIL') . ">";

      $sendResult = $client->sendEmail(
        $from_email,
        $user["userData"]["users_email"],
        $bCMS->cleanString($subject),
        $body
      );

      if ($sendResult->getErrorCode() == 0) {
        return parent::logEmail($user, $subject, $body);
      }
      return false;
    } catch (Exception $e) {
      trigger_error($e, E_USER_ERROR);
      return false;
    }
  }
}
