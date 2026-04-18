<?php
require_once __DIR__ . '/EmailHandler.php';

/**
 * Email handler for Cloudflare Email Service
 * Uses the Cloudflare REST API to send transactional emails
 * @see https://developers.cloudflare.com/email-service/api/send-emails/rest-api/
 */
class CloudflareEmailHandler extends EmailHandler
{
  public static function sendEmail($user, $subject, $body): bool
  {
    global $CONFIG, $bCMS, $CONFIGCLASS;

    if ($CONFIGCLASS->get('EMAILS_PROVIDERS_APIKEY') == false) {
      trigger_error("Email Provider is Cloudflare, but API Token not set", E_USER_WARNING);
      return true;
    }

    if ($CONFIGCLASS->get('EMAILS_PROVIDERS_CLOUDFLARE_ACCOUNT_ID') == false) {
      trigger_error("Email Provider is Cloudflare, but Account ID not set", E_USER_WARNING);
      return true;
    }

    $accountId = $CONFIGCLASS->get('EMAILS_PROVIDERS_CLOUDFLARE_ACCOUNT_ID');
    $apiToken = $CONFIGCLASS->get('EMAILS_PROVIDERS_APIKEY');

    $url = "https://api.cloudflare.com/client/v4/accounts/" . rawurlencode($accountId) . "/email/sending/send";

    $payload = json_encode([
      "to" => $user["userData"]["users_email"],
      "from" => [
        "email" => $CONFIGCLASS->get('EMAILS_FROMEMAIL'),
        "name" => $CONFIG['PROJECT_NAME'],
      ],
      "subject" => $bCMS->cleanString($subject),
      "html" => $body,
    ]);

    if ($payload === false) {
      trigger_error("Cloudflare Email: Failed to encode email payload as JSON: " . json_last_error_msg(), E_USER_WARNING);
      return false;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Authorization: Bearer " . $apiToken,
      "Content-Type: application/json",
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $responseBody = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($responseBody === false) {
      trigger_error("Cloudflare Email API request failed: " . $curlError, E_USER_WARNING);
      return false;
    }

    $response = json_decode($responseBody, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      $trimmedResponseBody = trim($responseBody);
      if (strlen($trimmedResponseBody) > 500) {
        $trimmedResponseBody = substr($trimmedResponseBody, 0, 500) . "...";
      }

      trigger_error(
        "Cloudflare Email API returned invalid JSON (HTTP $httpCode): " . json_last_error_msg() . ($trimmedResponseBody !== "" ? " - Response body: " . $trimmedResponseBody : ""),
        E_USER_WARNING
      );
      return false;
    }
    if ($httpCode >= 200 && $httpCode < 300 && isset($response['success']) && $response['success'] === true) {
      return parent::logEmail($user, $subject, $body);
    }

    // Build a meaningful error message from the API response
    $errorMessage = "Cloudflare Email API error (HTTP $httpCode)";
    if (isset($response['errors']) && is_array($response['errors'])) {
      $errors = array_map(function ($e) {
        return (isset($e['code']) ? $e['code'] . ': ' : '') . ($e['message'] ?? 'Unknown error');
      }, $response['errors']);
      $errorMessage .= " - " . implode("; ", $errors);
    }
    trigger_error($errorMessage, E_USER_WARNING);
    return false;
  }
}
