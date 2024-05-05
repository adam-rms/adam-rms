<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!isset($_POST['price_id'])) die("No price_id provided.");
if ($AUTH->data['users_userid'] !== $AUTH->data['instance']['instances_billingUser']) die("Sorry, you are not the billing contact for this business, please contact support.");

\Stripe\Stripe::setApiKey($CONFIGCLASS->get('STRIPE_KEY'));

$checkout_session = \Stripe\Checkout\Session::create([
  'line_items' => [[
    'price' => $_POST['price_id'],
    'quantity' => 1,
  ]],
  'customer_email' => $AUTH->data['users_email'],
  'metadata' => [
    'instance_id' => $AUTH->data['instance']['instances_id'],
  ],
  'allow_promotion_codes' => true,
  'mode' => 'subscription',
  'success_url' => $CONFIG['ROOTURL'] . '/api/instances/billing/postSubscribeDelay.php',
  'cancel_url' => $CONFIG['ROOTURL'] . '/',
  'subscription_data' => [
    'metadata' => [
      'instance_id' => $AUTH->data['instance']['instances_id'],
    ],
    'description' => 'AdamRMS Subscription for ' . $AUTH->data['instance']['instances_name'],
    'trial_settings' => ['end_behavior' => ['missing_payment_method' => 'pause']],
    'trial_period_days' => 7,
  ],
  'payment_method_collection' => 'always',
]);

header("HTTP/1.1 303 See Other");
header("Location: " . $checkout_session->url);
