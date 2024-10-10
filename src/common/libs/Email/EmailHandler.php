<?php

/**
 * Generic Email Provider that should be extended for each provider
 */
class EmailHandler
{

  /**
   * Send an email using this provider
   * @param mixed $user The user causing the email to be sent
   * @param mixed $subject Email subject
   * @param mixed $body HTML body to send
   * @return bool True if sent and logged in the DB, False otherwise
   */
  public static function sendEmail($user, $subject, $body): bool
  {
    throw new Exception("Not Implemented");
    // Actual implementations should call logEmail() after a successful email send
  }

  /**
   * Log the given email in the Database
   * @param mixed $user The user causing the email to be sent
   * @param mixed $subject Email subject
   * @param mixed $body HTML body to send
   * @return bool True if successfully logged, False otherwise
   */
  protected static function logEmail($user, $subject, $body)
  {
    global $DBLIB, $CONFIG, $bCMS, $CONFIGCLASS;
    $sqldata = array(
      "users_userid" => $user['userData']['users_userid'],
      "emailSent_html" => $body,
      "emailSent_subject" => $bCMS->cleanString($subject),
      "emailSent_sent" => date('Y-m-d G:i:s'),
      "emailSent_fromEmail" => $CONFIGCLASS->get('EMAILS_FROMEMAIL'),
      "emailSent_fromName" => $CONFIG['PROJECT_NAME'],
      'emailSent_toEmail' => $user["userData"]["users_email"],
      'emailSent_toName' => $user["userData"]["users_name1"] .  ' ' . $user["userData"]["users_name2"]
    );
    $emailid = $DBLIB->insert('emailSent', $sqldata);
    if (!$emailid) return false;
    else return true;
  }
}
