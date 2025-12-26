<?php
require_once __DIR__ . '/../apiHeadSecure.php';
require_once __DIR__ . '/../../common/libs/LabelPrinters/CUPSPrinter.php';

if (!$AUTH->instancePermissionCheck("ASSETS:LABEL_PRINTERS:PRINT")) {
    finish(false, ["code" => "AUTH", "message" => "No permission to print labels"]);
}

if (!isset($_POST['labelPrinters_id']) || !isset($_POST['labelTemplates_id']) || !isset($_POST['assets_ids'])) {
    finish(false, ["code" => "MISSING-FIELDS", "message" => "Missing required fields"]);
}

$printerId = (int)$_POST['labelPrinters_id'];
$templateId = (int)$_POST['labelTemplates_id'];
$assetIds = $_POST['assets_ids']; // Array

if (!is_array($assetIds) || empty($assetIds)) {
    finish(false, ["code" => "INVALID-ASSETS", "message" => "No assets specified"]);
}

// Load printer
$DBLIB->where("labelPrinters_id", $printerId);
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("labelPrinters_deleted", 0);
$printer = $DBLIB->getOne("labelPrinters");

if (!$printer) {
    finish(false, ["code" => "PRINTER-NOT-FOUND", "message" => "Printer not found"]);
}

// Load template
$DBLIB->where("labelTemplates_id", $templateId);
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("labelTemplates_deleted", 0);
$template = $DBLIB->getOne("labelTemplates");

if (!$template) {
    finish(false, ["code" => "TEMPLATE-NOT-FOUND", "message" => "Template not found"]);
}

// Initialize CUPS printer
try {
    $cupsPrinter = new CUPSPrinter($DBLIB, $printerId);
} catch (Exception $e) {
    finish(false, ["code" => "PRINTER-INIT-FAIL", "message" => $e->getMessage()]);
}

$results = [];
$successCount = 0;
$failCount = 0;

foreach ($assetIds as $assetId) {
    // Load asset data
    $DBLIB->where("assets_id", $assetId);
    $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("assets_deleted", 0);
    $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
    $asset = $DBLIB->getOne("assets", null, [
        "assets.*",
        "assetTypes.assetTypes_name",
        "manufacturers.manufacturers_name"
    ]);

    if (!$asset) {
        $results[] = ['asset_id' => $assetId, 'success' => false, 'error' => 'Asset not found'];
        $failCount++;
        continue;
    }

    // Get barcode for asset
    $DBLIB->where("assets_id", $assetId);
    $DBLIB->where("assetsBarcodes_deleted", 0);
    $DBLIB->orderBy("assetsBarcodes_added", "DESC");
    $barcode = $DBLIB->getOne("assetsBarcodes");

    if (!$barcode) {
        // Create a barcode if none exists
        $barcodeValue = $asset['assets_tag'];
        $barcodeType = 'CODE_128';

        $barcodeId = $DBLIB->insert("assetsBarcodes", [
            'assets_id' => $assetId,
            'assetsBarcodes_value' => $barcodeValue,
            'assetsBarcodes_type' => $barcodeType,
            'users_userid' => $AUTH->data['users_userid']
        ]);

        $barcode = [
            'assetsBarcodes_value' => $barcodeValue,
            'assetsBarcodes_type' => $barcodeType
        ];
    }

    // Prepare label data
    $labelData = [
        'asset_tag' => $asset['assets_tag'],
        'asset_type' => $asset['assetTypes_name'] ?? '',
        'manufacturer' => $asset['manufacturers_name'] ?? '',
        'barcode_value' => $barcode['assetsBarcodes_value'],
        'barcode_type' => $barcode['assetsBarcodes_type'],
        'qr_url' => $CONFIG['ROOTURL'] . '/asset.php?id=' . $asset['assetTypes_id'] . '&asset=' . $assetId,
        'instance_name' => $AUTH->data['instance']['instances_name']
    ];

    // Print label
    $printResult = $cupsPrinter->print($template, $labelData);

    // Log print job
    $jobStatus = $printResult['success'] ? 'success' : 'failed';
    $DBLIB->insert("labelPrintJobs", [
        'instances_id' => $AUTH->data['instance']['instances_id'],
        'labelPrinters_id' => $printerId,
        'labelTemplates_id' => $templateId,
        'assets_id' => $assetId,
        'users_userid' => $AUTH->data['users_userid'],
        'labelPrintJobs_status' => $jobStatus,
        'labelPrintJobs_data' => json_encode($labelData),
        'labelPrintJobs_error' => $printResult['success'] ? null : $printResult['error']
    ]);

    $results[] = [
        'asset_id' => $assetId,
        'asset_tag' => $asset['assets_tag'],
        'success' => $printResult['success'],
        'error' => $printResult['success'] ? null : $printResult['error']
    ];

    if ($printResult['success']) {
        $successCount++;
    } else {
        $failCount++;
    }
}

finish(true, null, [
    'total' => count($assetIds),
    'success' => $successCount,
    'failed' => $failCount,
    'results' => $results
]);

/** @OA\Post(
 *     path="/api/labelPrinters/print.php",
 *     summary="Print asset labels",
 *     description="Print labels for one or more assets using specified printer and template",
 *     operationId="printLabels",
 *     tags={"labelPrinters"},
 *     @OA\Response(
 *         response="200",
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *     ),
 *     @OA\Response(
 *         response="default",
 *         description="Error",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="result",
 *                     type="boolean",
 *                     description="Whether the request was successful",
 *                 ),
 *                 @OA\Property(
 *                     property="error",
 *                     type="array",
 *                     description="An Array containing an error code and a message",
 *                 ),
 *             ),
 *         ),
 *     ),
 *     @OA\Parameter(
 *         name="labelPrinters_id",
 *         in="query",
 *         description="The printer ID",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *     ),
 *     @OA\Parameter(
 *         name="labelTemplates_id",
 *         in="query",
 *         description="The template ID",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *     ),
 *     @OA\Parameter(
 *         name="assets_ids",
 *         in="query",
 *         description="Array of asset IDs to print",
 *         required=true,
 *         @OA\Schema(type="array", @OA\Items(type="integer")),
 *     ),
 * )
 */