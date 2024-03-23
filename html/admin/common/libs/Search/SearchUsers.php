<?php
require_once __DIR__ . '/BaseSearch.php';

class SearchUsers extends BaseSearch
{
    /**
     * Load the users for searching
     *
     * @return array An array of search objects
     */
    public function load(): array
    {
        // The user cannot access users
        if (!$this->AUTH->instancePermissionCheck("BUSINESS:USERS:VIEW:INDIVIDUAL_USER") and !$this->AUTH->serverPermissionCheck("USERS:EDIT")) {
            return [];
        }

        $this->DBLIB->where("users.users_deleted", 0);
        $this->DBLIB->where("users.users_suspended", 0);
        if (!$this->AUTH->serverPermissionCheck("USERS:EDIT")) {
            $this->DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
            $this->DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
            $this->DBLIB->where("userInstances.userInstances_deleted",  0);
            $this->DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
            $this->DBLIB->where("instancePositions.instances_id",  $this->AUTH->data['instance']['instances_id']);
        }
        $users = $this->DBLIB->get("users", null, ['users.users_userid', 'users_username', 'users_name1', 'users_name2']);

        // Generate the array and return the function
        return array_map(function ($user) {

            // Return the data to the list
            return [
                'type' => "user",
                'searchable' => [
                    $user['users_username'],
                    $user['users_name1'],
                    $user['users_name2']
                ],
                'title' => $user['users_name1'] . " " . $user['users_name2'],
                'data' => $user
            ];
        }, $users);

    }
}
