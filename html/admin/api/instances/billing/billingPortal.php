<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if ($AUTH->data['users_userid'] !== $AUTH->data['instance']['instances_billingUser']) die("Sorry, you are not the billing contact for this business, please contact support.");

$stripe = new \Stripe\StripeClient($CONFIGCLASS->get('STRIPE_KEY'));
$link = $stripe->billingPortal->sessions->create([
  'customer' => $AUTH->data['instance']['instances_planStripeCustomerId'],
  'return_url' => $CONFIG['ROOTURL'] . "/instances/billing.php",
]);


header("HTTP/1.1 303 See Other");
header("Location: " . $link->url);
