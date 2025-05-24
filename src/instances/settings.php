<?php
require_once __DIR__ . '/../common/headSecure.php';

$PAGEDATA['pageConfig'] = ["TITLE" => "Business Basic Settings", "BREADCRUMB" => false];

if (!$AUTH->instancePermissionCheck("BUSINESS:BUSINESS_SETTINGS:VIEW")) die($TWIG->render('404.twig', $PAGEDATA));

$DBLIB->orderBy("assetsAssignmentsStatus_order", "ASC");
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assetsAssignmentsStatus_deleted", 0);
$PAGEDATA['USERDATA']['instance']['assetStatus'] = $DBLIB->get("assetsAssignmentsStatus");

// Fetch unit_system
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$instanceData = $DBLIB->getOne("instances");
$PAGEDATA['USERDATA']['instance']['unit_system'] = $instanceData['unit_system'] ?? 'metric';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['page']) && $_POST['page'] == "instances_settings") {
    if (!$AUTH->instancePermissionCheck("BUSINESS:BUSINESS_SETTINGS:EDIT")) {
        $PAGEDATA['errors'][] = "You do not have permission to edit settings.";
    } else {
        // Handle unit_system update
        if (isset($_POST['unit_system'])) {
            $unit_system = $_POST['unit_system'];
            if (in_array($unit_system, ['metric', 'imperial'])) {
                $DBLIB->where('instances_id', $AUTH->data['instance']['instances_id']);
                $DBLIB->update('instances', ['unit_system' => $unit_system]);
                $PAGEDATA['USERDATA']['instance']['unit_system'] = $unit_system; // Update for immediate display
                $PAGEDATA['successes'][] = "Unit system updated successfully.";
            } else {
                $PAGEDATA['errors'][] = "Invalid unit system selected.";
            }
        }
        // After processing, check if it's an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            $response = [];
            if (!empty($PAGEDATA['errors'])) {
                $response['status'] = 'error';
                $response['messages'] = $PAGEDATA['errors'];
            } else {
                $response['status'] = 'success';
                $response['messages'] = $PAGEDATA['successes'] ?? ['Settings updated successfully.'];
                // Optionally, include the updated data if needed by the frontend
                // $response['data'] = ['unit_system' => $PAGEDATA['USERDATA']['instance']['unit_system']];
            }
            echo json_encode($response);
            exit;
        }
    }
}

echo $TWIG->render('instances/instances_settings.twig', $PAGEDATA);
?>
