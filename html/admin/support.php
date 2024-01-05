<?php
require_once __DIR__ . '/common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Support", "BREADCRUMB" => true];

function freshdeskCall($url) {
  global $CONFIG;
  $url = $CONFIG['FRESHDESK']['URL'] . "/api/v2/" . $url;
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_HEADER, true);
  curl_setopt($ch, CURLOPT_USERPWD, $CONFIG['FRESHDESK']['APIKEY'] . ":x");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $server_output = curl_exec($ch);
  $info = curl_getinfo($ch);
  $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
  $headers = substr($server_output, 0, $header_size);
  $response = substr($server_output, $header_size);
  curl_close($ch);
  if($info['http_code'] == 200) {
    return json_decode($response,true);
  } else {
    return [];
  }
}

$ticketStatus = [2=>"Open",3=>"Pending",4=>"Resolved",5=>"Closed"];
$ticketPriority = [1=>"Low",2=>"Medium",3=>"High",4=>"Urgent"];
$ticketSource = [1=>"Email",2=>"Portal",3=>"Phone",7=>"Chat",9=>"Widget",10=>"Outbound Email"];

if ($PAGEDATA['USERDATA']['users_emailVerified'] === 1 and $CONFIG['FRESHDESK']['APIKEY'] != null and $CONFIG['FRESHDESK']['URL'] != null) $tickets = freshdeskCall("tickets?order_by=created_at&include=description&order_type=desc&email=" . urlencode($PAGEDATA['USERDATA']['users_email']));
else $tickets = [];

$PAGEDATA['TICKETS'] = [];
foreach ($tickets as $ticket) {
  $ticket['conversation'] = freshdeskCall("tickets/" . $ticket['id'] . "/conversations"); //TODO improve performance here
  $ticket['status'] = $ticketStatus[$ticket['status']];
  $ticket['priority'] = $ticketPriority[$ticket['priority']];
  $ticket['source'] = $ticketSource[$ticket['source']];
  $PAGEDATA['TICKETS'][] = $ticket; 
}


echo $TWIG->render('support.twig', $PAGEDATA);
?>
