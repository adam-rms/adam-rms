<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['term'])) {
    finish(false, ["message" => "Missing search term", "code" => "MISSINGTERM"]);
}

$searchTerm = '%' . $_POST['term'] . '%';

// Search locations by name
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("locations_deleted", 0);
$DBLIB->where("locations_name", $searchTerm, 'LIKE');
$DBLIB->orderBy("locations_name", "ASC");
$locations = $DBLIB->get("locations", 20, ["locations_id", "locations_name"]);

$results = [];
if ($locations) {
    foreach ($locations as $location) {
        $results[] = [
            "id" => $location['locations_id'],
            "label" => $location['locations_name'],
            "value" => $location['locations_name']
        ];
    }
}

finish(true, null, $results);