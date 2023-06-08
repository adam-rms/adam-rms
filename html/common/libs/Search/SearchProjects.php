<?php
require_once __DIR__ . '/BaseSearch.php';

class SearchProjects extends BaseSearch
{
    /**
     * Load the projects that are accessible
     *
     * @return array An array of search objects
     */
    public function load(): array
    {
        // The user cannot access projects
        if (!$this->AUTH->instancePermissionCheck("PROJECTS:VIEW")) {
            return [];
        }

        // Get the projects
        $this->DBLIB->where("projects.instances_id", $this->AUTH->data['instance']['instances_id']);
        $this->DBLIB->where("projects.projects_deleted", 0);
        $this->DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
        $this->DBLIB->join("users", "projects.projects_manager=users.users_userid",);
        $projects = $this->DBLIB->get("projects", null, ["projects_id", "projects_archived", "projects_name", "projects_description", "clients_name", "projects_dates_deliver_start", "projects_dates_deliver_end", "projects_dates_use_start", "projects_dates_use_end", "projects_manager as projects_manager_id", "concat(users_name1, ' ', users_name2) AS projects_manager"]);

        // Generate the array and return the function
        return array_map(function ($project) {
            // Create a list of all set project dates
            $dates = array_unique(array_merge(
                ($project['projects_dates_deliver_start'] ? [strtotime($project['projects_dates_deliver_start'])] : []),
                ($project['projects_dates_deliver_end'] ? [strtotime($project['projects_dates_deliver_end'])] : []),
                ($project['projects_dates_use_start'] ? [strtotime($project['projects_dates_use_start'])] : []),
                ($project['projects_dates_use_end'] ? [strtotime($project['projects_dates_use_end'])] : [])
            ));

            // Convert the set project dates into strings (to be able to search)
            $readable_dates = [];
            foreach ($dates as $date) {
                array_push($readable_dates, date("l jS \of F Y h:i:s A", $date));
                array_push($readable_dates, date("Y-m-d H:i:s", $date));
            }

            // Generate the except for the project
            $except = "The project is called '" . $project['projects_name'] . "', and is being project managed by " . $project['projects_manager'] . ".";
            if ($project['projects_dates_deliver_start'] and $project['projects_dates_deliver_end']) {
                $except .= " The project runs from " . date("Y-m-d H:i:s", strtotime($project['projects_dates_deliver_start'])) . " to " . date("Y-m-d H:i:s", strtotime($project['projects_dates_deliver_end'])) . ".";
            }
            if ($project['projects_dates_use_start'] and $project['projects_dates_use_end']) {
                $except .= " The assets are in use from " . date("Y-m-d H:i:s", strtotime($project['projects_dates_use_start'])) . " to " . date("Y-m-d H:i:s", strtotime($project['projects_dates_use_end'])) . ".";
            }

            // Return the data
            return [
                'type' => "project",
                'searchable' => array_merge([
                    $project['projects_name'],
                    $project['projects_description'],
                    $project['projects_manager'],
                    $project['clients_name'],
                ], $readable_dates),
                'title' => $project['projects_name'],
                'except' => $except,
                'data' => $project
            ];

        }, $projects);

    }
}