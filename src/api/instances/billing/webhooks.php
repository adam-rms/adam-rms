<?php
require_once __DIR__ . '/../../apiHead.php';

\Stripe\Stripe::setApiKey($CONFIGCLASS->get('STRIPE_KEY'));
$stripe = new \Stripe\StripeClient($CONFIGCLASS->get('STRIPE_KEY'));

function handleWebhook($subscription)  // contains a \Stripe\Subscription
{
  global $DBLIB, $stripe;  
  $status = $subscription->status;
  $instanceid = $subscription->metadata->instance_id;
  if (!$instanceid || !is_numeric($instanceid)) throw new Exception("Invalid instance_id recieved from subscription metadata.");
  $DBLIB->where('instances_id', $instanceid);
  $instance = $DBLIB->getOne('instances', ['instances_id', 'instances_name', 'instances_planStripeCustomerId']);
  if (!$instance) throw new Exception("Instance not found in database.");
  $instanceUpdateData = [];
  if ($instance['instances_planStripeCustomerId'] !== $subscription->customer) {
    // Change the customer over - TODO this is not ideal - we ideally need to figure out why the customer id has changed.
    $instanceUpdateData['instances_planStripeCustomerId'] = $subscription->customer;
  }
  if ($status === "incomplete" || $status === "incomplete_expired" || $status === "past_due" || $status === "unpaid" || $status === "paused") {
    $instanceUpdateData['instances_suspended'] = 1;
    $instanceUpdateData['instances_suspendedReason'] = "as payment is required for the subscription";
    $instanceUpdateData['instances_suspendedReasonType'] = "billing";
  } else if ($status === "trialing" || $status === "active") {
    // Find the relevant product in the subscription and update the instance with the limits.
    foreach ($subscription->items->data as $item) {
      $thisProduct = $stripe->products->retrieve($item->plan->product, []);
      if ($thisProduct and count($thisProduct->metadata) > 0 and $thisProduct->metadata['product'] === 'AdamRMS' and $thisProduct->metadata['instances_storageLimit'] and $thisProduct->metadata['instances_assetLimit'] and $thisProduct->metadata['instances_userLimit'] and $thisProduct->metadata['instances_projectLimit']) {
        $instanceUpdateData['instances_planName'] = $thisProduct->name;
        $instanceUpdateData['instances_suspended'] = 0;
        $instanceUpdateData['instances_suspendedReason'] = "";
        $instanceUpdateData['instances_suspendedReasonType'] = "";
        $instanceUpdateData['instances_storageLimit'] = $thisProduct->metadata['instances_storageLimit'];
        $instanceUpdateData['instances_assetLimit'] = $thisProduct->metadata['instances_assetLimit'];
        $instanceUpdateData['instances_userLimit'] =  $thisProduct->metadata['instances_userLimit'];
        $instanceUpdateData['instances_projectLimit'] = $thisProduct->metadata['instances_projectLimit'];
        $instanceUpdateData['instances_storageEnabled'] = $thisProduct->metadata['instances_storageEnabled'];
        break;
      }
    }
  } else if ($status === "canceled") {
    $instanceUpdateData['instances_planName'] = "";
    $instanceUpdateData['instances_suspended'] = 1;
    $instanceUpdateData['instances_suspendedReason'] = "as the subscription has been canceled";
    $instanceUpdateData['instances_suspendedReasonType'] = "noplan";
    $instanceUpdateData['instances_storageLimit'] = 1;
    $instanceUpdateData['instances_storageEnabled'] = 0;
    $instanceUpdateData['instances_assetLimit'] = 1;
    $instanceUpdateData['instances_userLimit'] = 1;
    $instanceUpdateData['instances_projectLimit'] = 1;
  }
  $DBLIB->where('instances_id', $instanceid);
  if (!$DBLIB->update('instances', $instanceUpdateData, 1)) throw new Exception("Failed to update instance in database.");
}

try {
  $payload = @file_get_contents('php://input');
  $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
  $event = \Stripe\Webhook::constructEvent(
    $payload,
    $sig_header,
    $CONFIGCLASS->get('STRIPE_WEBHOOK_SECRET')
  );
} catch (\Stripe\Exception\SignatureVerificationException $e) {
  // Invalid signature
  echo 'Webhook error while validating signature.';
  http_response_code(400);
  exit();
} catch (\UnexpectedValueException $e) {
  // Invalid payload
  echo 'Webhook error while parsing basic request.';
  http_response_code(400);
  exit();
}

// Handle the event
switch ($event->type) {
  case 'customer.subscription.created':
    $subscription = $event->data->object;
    handleWebhook($subscription);
    break;
  case 'customer.subscription.deleted':
    $subscription = $event->data->object;
    handleWebhook($subscription);
    break;
  case 'customer.subscription.updated':
    $subscription = $event->data->object;
    handleWebhook($subscription);
    break;
  default:
    // Unexpected event type
    continue;
}
