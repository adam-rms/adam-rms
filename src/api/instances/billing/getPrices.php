<?php
require_once __DIR__ . '/../../apiHead.php';

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
  if (!$product->default_price) continue;
  $priceData = $stripe->prices->retrieve($product->default_price, ["expand" => ['currency_options']]);
  if (!$priceData->active || $priceData->type !== 'recurring') continue;
  $productReturn = [
    'id' => $product->id,
    'name' => $product->name,
    'description' => $product->description,
    'metadata' => $product->metadata,
    'marketing_features' => $product->marketing_features,
    'price_id' => $priceData->id,
    'time_period' => $priceData->recurring->interval,
    'price' => []
  ];
  foreach (json_decode(json_encode($priceData->currency_options), true) as $currency => $amount) {
    $moneyAmount = new Money($amount['unit_amount'], new Currency(strtoupper($currency)));
    $currencies = new ISOCurrencies();
    $numberFormatter = new NumberFormatter('en_GB', NumberFormatter::CURRENCY);
    $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);
    $priceAmount = $moneyFormatter->format($moneyAmount);
    $productReturn['price'][] = [
      'formatted_amount' => $priceAmount,
      'unit_amount' => $amount['unit_amount'],
      'currency' => $currency,
    ];
  }
  $productsReturn[] = $productReturn;
}

usort($productsReturn, fn($a, $b) => $a['price'][0]['unit_amount'] <=> $b['price'][0]['unit_amount']);

finish(true, null, $productsReturn);
