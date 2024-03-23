<?php
require_once __DIR__ . '/BaseSearch.php';

class SearchClients extends BaseSearch
{
    /**
     * Load the clients for searching
     *
     * @return array An array of search objects
     */
    public function load(): array
    {
        // The user cannot access Clients
        if (!$this->AUTH->instancePermissionCheck("CLIENTS:VIEW")) {
            return [];
        }

        $this->DBLIB->where("clients_deleted", 0);
        $this->DBLIB->where("instances_id", $this->AUTH->data['instance']['instances_id']);
        $clients = $this->DBLIB->get("clients", null, ['clients_id', 'clients_name', 'clients_website', 'clients_email', 'clients_notes', 'clients_address', 'clients_phone']);

        // Generate the array and return the function
        return array_map(function ($client) {

            // Return the data to the list
            return [
                'type' => "client",
                'searchable' => [
                    $client['clients_name'],
                    $client['clients_website'],
                    $client['clients_email'],
                    $client['clients_notes'],
                    $client['clients_address'],
                    $client['clients_phone'],
                ],
                'title' => $client['clients_name'],
                'except'=> mb_strimwidth($client['clients_notes'], 0, 200, "..."),
                'data' => $client
            ];
        }, $clients);

    }
}
