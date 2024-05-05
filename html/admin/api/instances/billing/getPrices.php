<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if ($AUTH->data['users_userid'] !== $AUTH->data['instance']['instances_billingUser']) die("Sorry, you are not the billing contact for this business, please contact support.");

$stripe = new \Stripe\StripeClient($CONFIGCLASS->get('STRIPE_KEY'));
$products = $stripe->products->search([
  'query' => 'active:\'true\' AND metadata[\'showInDashboard\']:\'true\' AND metadata[\'product\']:\'AdamRMS\'',
  'limit' => 100
]);
$productsReturn = [];

use Money\Currency;
use Money\Money;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;

foreach ($products->data as $product) {
  $price = $stripe->prices->retrieve($product->default_price, []);
  $amount = new Money($price->unit_amount, new Currency(strtoupper($price->currency)));
  $currencies = new ISOCurrencies();
  $numberFormatter = new NumberFormatter('en_GB', NumberFormatter::CURRENCY);
  $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);
  $priceAmount = $moneyFormatter->format($amount);
  $productReturn = [
    'id' => $product->id,
    'name' => $product->name,
    'description' => $product->description,
    'metadata' => $product->metadata,
    'marketing_features' => $product->marketing_features,
    'price' => [
      'id' => $price->id,
      'formatted_amount' => $priceAmount,
      'unit_amount' => $price->unit_amount,
      'currency' => $price->currency,
      'time_period' => $price->recurring->interval,
    ]
  ];
  $productsReturn[] = $productReturn;
}

usort($productsReturn, fn ($a, $b) => $a['price']['unit_amount'] <=> $b['price']['unit_amount']);

finish(true, null, $productsReturn);
