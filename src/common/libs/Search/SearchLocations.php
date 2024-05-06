<?php
require_once __DIR__ . '/BaseSearch.php';

class SearchLocations extends BaseSearch
{
    /**
     * Load the clients for searching
     *
     * @return array An array of search objects
     */
    public function load(): array
    {
        // The user cannot access Locations
        if (!$this->AUTH->instancePermissionCheck("LOCATIONS:VIEW")) {
            return [];
        }

        $this->DBLIB->where("locations.instances_id", $this->AUTH->data['instance']['instances_id']);
        $this->DBLIB->where("locations.locations_deleted", 0);
        $this->DBLIB->join("clients","locations.clients_id=clients.clients_id","LEFT");
        $locations = $this->DBLIB->get('locations', null, ["locations_id", "locations_name", "locations.clients_id as clients_id", "clients_name",  "locations_address", "locations_deleted", "locations_subOf", "locations_notes" ]);

        // Generate the array and return the function
        return array_map(function ($location) {

            // Return the data to the list
            return [
                'type' => "location",
                'searchable' => [
                    $location["locations_name"],
                    $location["locations_address"],
                    $location["locations_deleted"],
                    $location["locations_notes"],
                    $location["clients_name"],
                ],
                'title' => $location['locations_name'],
                'except'=> mb_strimwidth($location["locations_notes"], 0, 200, "..."),
                'data' => $location
            ];
        }, $locations);
    }
}
