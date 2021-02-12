<?php
require_once __DIR__ . '/../apiHeadSecure.php';

// USER OPTIONS
// Replace these values with ones appropriate to you.
$accessKeyId = $CONFIG['AWS']['FINEUPLOADER']['KEY'];
$secretKey = $CONFIG['AWS']['FINEUPLOADER']['SECRET'];
$region = $CONFIG['AWS']['DEFAULTUPLOADS']['REGION'];
//$acl = 'public'; // private, public-read, etc
// VARIABLES
// These are used throughout the request.


// POST POLICY
// Amazon requires a base64-encoded POST policy written in JSON.
// This tells Amazon what is acceptable for this request. For
// simplicity, we set the expiration date to always be 24H in
// the future. The two "starts-with" fields are used to restrict
// the content of "key" and "Content-Type", which are specified
// later in the POST fields. Again for simplicity, we use blank
// values ('') to not put any restrictions on those two fields.
function generatePolicy($inputObject) {
    global $accessKeyId,$region;
    $shortDate = gmdate('Ymd');
    $credential = $accessKeyId . '/' . $shortDate . '/' . $region . '/s3/aws4_request';
    $policyArray = $inputObject;
    $policyArray['conditions'][] = ['x-amz-algorithm' => 'AWS4-HMAC-SHA256'];
    $policyArray['conditions'][] = ['x-amz-credential' => $credential];
    $policyArray['expiration'] = gmdate('Y-m-d\TH:i:s\Z', time() + 86400);
    return base64_encode(json_encode($policyArray));
}
function signV4RestRequest($policy) {
    global $secretKey,$region;
    $shortDate = gmdate('Ymd');
    $signingKey = hash_hmac('sha256', $shortDate, 'AWS4' . $secretKey, true);
    $signingKey = hash_hmac('sha256', $region, $signingKey, true);
    $signingKey = hash_hmac('sha256', 's3', $signingKey, true);
    $signingKey = hash_hmac('sha256', 'aws4_request', $signingKey, true);
    return hash_hmac('sha256', $policy, $signingKey);
}

header('Content-Type: application/json');
$responseBody = file_get_contents('php://input');
$contentAsObject = json_decode($responseBody, true);
$jsonContent = json_encode($contentAsObject);
if (isset($contentAsObject["headers"])) $headersStr = $contentAsObject["headers"];
else $headersStr = false;
if ($headersStr) {
    echo json_encode(["response"=>"error"]);
} else {
    $policyObj = json_decode($jsonContent, true);
    $encodedPolicy = generatePolicy($policyObj);
    $response = array('policy' => $encodedPolicy, 'signature' => signV4RestRequest($encodedPolicy));
    echo json_encode($response);
}
?>