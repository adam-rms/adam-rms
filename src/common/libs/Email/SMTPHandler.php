<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/EmailHandler.php';

class SMTPEmailHandler extends EmailHandler
{

  public static function sendEmail($user, $subject, $body): bool
  {
    global $CONFIG, $CONFIGCLASS;
    $emailFromDomain = parent::domainFromEmail($CONFIGCLASS->get('EMAILS_FROMEMAIL'));
    try {
      $mail = new PHPMailer(true);

      // Don't log in prod
      $mail->SMTPDebug = SMTP::DEBUG_OFF;

      //Server settings
      $mail->isSMTP();
      $mail->Host = $CONFIGCLASS->get('EMAILS_SMTP_SERVER');
      $mail->Port = $CONFIGCLASS->get('EMAILS_SMTP_PORT');
      if ($CONFIGCLASS->get('EMAILS_SMTP_USERNAME') && $CONFIGCLASS->get('EMAILS_SMTP_PASSWORD')) {
        $mail->SMTPAuth = true;
        $mail->Username = $CONFIGCLASS->get('EMAILS_SMTP_USERNAME');
        $mail->Password = $CONFIGCLASS->get('EMAILS_SMTP_PASSWORD');
      } else {
        $mail->SMTPAuth = false;
      }

      $encryptionType = $CONFIGCLASS->get('EMAILS_SMTP_ENCRYPTION');
      if ($encryptionType === 'None') {
        $mail->SMTPSecure = false;
      } elseif ($encryptionType === 'TLS') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Default to SMTPS for backward compatibility
      }

      //Recipients
      $mail->setFrom($CONFIGCLASS->get('EMAILS_FROMEMAIL'), $CONFIG['PROJECT_NAME']);
      $mail->addAddress($user["userData"]["users_email"], $user["userData"]["users_name1"] .  ' ' . $user["userData"]["users_name2"]);

      //Content
      $mail->isHTML(true);
      $mail->Subject = $subject;
      $mail->Body    = $body;

      $sent = $mail->send();
      if ($sent) {
        return parent::logEmail($user, $subject, $body);
      } else {
        trigger_error($mail->ErrorInfo, E_USER_ERROR);
      }
      return false;
    } catch (Exception $e) {
      trigger_error($e, E_USER_ERROR);
      return false;
    }
  }
}
