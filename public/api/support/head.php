<?php
require_once __DIR__ . '/../apiHeadSecure.php';

function freshdeskSubmit($url,$data) {
  global $CONFIG;
  $url = $CONFIG['FRESHDESK']['URL'] . "/api/v2/" . $url;
  $ch = curl_init($url);
  $header[] = "Content-type: application/json";
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_USERPWD, $CONFIG['FRESHDESK']['APIKEY'] . ":x");
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $server_output = curl_exec($ch);
  $info = curl_getinfo($ch);
  $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
  $headers = substr($server_output, 0, $header_size);
  $response = substr($server_output, $header_size);
  curl_close($ch);
  if($info['http_code'] == 201) {
    return true;
  } else {
    return false;
  }
}