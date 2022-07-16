<?php

require_once __DIR__ . '/BaseSearch.php';
require_once __DIR__ . '/SearchCMS.php';
require_once __DIR__ . '/SearchProjects.php';
require_once __DIR__ . '/SearchClients.php';
require_once __DIR__ . '/SearchLocations.php';
require_once __DIR__ . '/SearchUsers.php';

class Search extends BaseSearch
{

    protected array $providers;

    public function __construct(?int $instance_id = null)
    {
        parent::__construct($instance_id);

        $this->providers=[
            new SearchCMS($instance_id),
            new SearchProjects($instance_id),
            new SearchClients($instance_id),
            new SearchLocations($instance_id),
            new SearchUsers($instance_id)
        ];
    }

    /**
     * Run load on each of the providers
     *
     * @return array An array of search objects
     */
    public function load(): array
    {

        $output = [];
        foreach ($this->providers as $provider){
            $output = array_merge($output, $provider->load());
        }
        return $output;
    }
}