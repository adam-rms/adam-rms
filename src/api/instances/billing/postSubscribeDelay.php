<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

// Used whilst we wait for the stripe webhook
header("Content-Type: text/html");
echo "Please wait a moment whilst we process your subscription. You will be redirected to AdamRMS shortly.";
echo "<script>window.setTimeout(function () {location.href = '" . $CONFIG['ROOTURL'] . "/';}, 5000);</script>";