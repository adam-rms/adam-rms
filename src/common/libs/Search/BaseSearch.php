<?php
use Fuse\Fuse;

class BaseSearch
{
    protected MysqliDb $DBLIB;
    protected $AUTH;
    protected array $instance;
    protected array $items;


    /**
     * BaseSearch constructor.
     * @param int|null $instance_id The instance ID. Set to null if the auth instance should be used
     * @throws Exception Throws an exception if the user is not logged in or if the instance does not exist.
     */
    public function __construct(?int $instance_id=null)
    {
        global $DBLIB, $AUTH;
        $this->DBLIB = $DBLIB;
        $this->AUTH = $AUTH;

        $this->_evaluate_instance_id($instance_id);

    }

    /**
     * Use a fuzzy search to searched the data source. When the data is loaded, it is cached to speed up future
     * searches in the same object.
     *
     * @param string $term The search term
     * @param int $limit The maximum number of items to return
     * @param int $offset The page offset
     * @return array An array with the 'results'
     */
    public function search(string $term, int $limit=20, int $offset=0): array
    {
        $start_time = microtime (true);

        // Only load items if it is not set
        if(!isset($this->items)){
            $this->items = $this->load();
        }

        // Set the fuse options
        $options = [
            'keys' => ['searchable'],
            'includeScore'=>false,
            'includeMatches'=>true,
            'shouldSort'=>true
        ];

        // Search the items and get the number of results
        $fuse = new Fuse($this->items, $options);
        $results = $fuse->search($term);
        $total_results = count($results);

        // Paginate the results so we get the correct number
        $paginated_results = array_slice($results, $offset, $limit);

        // Clean the results for the api
        $cleaned_results = array_map(function ($item) {
            unset($item['item']['searchable']);
            return $item['item'];

        }, $paginated_results);

        return [
            'total'=>$total_results,
            'offset'=>$offset,
            'limit'=>$limit,
            'speed'=> microtime(true)-$start_time,
            'results'=>$cleaned_results
        ];
    }


    /**
     * This function should be overloaded in a child class. It should load the searchable data from the data source
     * (normally the database) and should return an array with the data. Each object in the array should be in the
     * following format:
     *
     * [
     *      'type'=> <STRING DEFINING THE DATA TYPE>,
     *      'searchable'=>[<ARRAY OF STRINGS THAT ARE SEARCHED>],
     *      'title' => <STRING TITLE FOR THE ITEM>,
     *      'except'=> <OPTIONAL STRING TO DEFINE A EXCEPT FOR THE ITEM>,
     *      'data'=> <ANY OTHER RELEVANT DATA TO THE DATA TYPE>
     * ]
     *
     * @return array An array of objects that are in the format above
     */
    public function load(): array{
        // Overload this
        return [];
    }

    /**
     * Reload cached items
     */
    function refresh(){
        $this->items = $this->load();
    }

    /**
     * Sets a new instance for a new search, the refreshes the cached items for the new instance
     *
     * @param int|null $instance_id The new id of the instance
     * @throws Exception Throws an exception if the user is not logged in or if the instance does not exist.
     */
    public function setInstanceId(?int $instance_id) {
        $this->_evaluate_instance_id($instance_id);
        $this->refresh();
    }

    /**
     *
     * Check the instance exits, load it and save it to $this->instance
     *
     * @param int|null $instance_id The instance ID. Set to null if the auth instance should be used
     * @throws Exception Throws an exception if the user is not logged in or if the instance does not exist.
     */
    private function _evaluate_instance_id (?int $instance_id=null)
    {

        //Evaluate instance id
        if ($instance_id == null and $this->AUTH->login) {
             $instance_id = $this->AUTH->data['instance']['instances_id'];
        }

        if($instance_id == null){
            throw new ParseError("User not logged in");
        }

        $this->DBLIB->where("instances_id",$instance_id);
        $this->DBLIB->where("instances_deleted",0);
        $instance = $this->DBLIB->getone("instances",['instances_publicConfig','instances_id','instances_config_currency']);

        if (!$instance) throw new ValueError("Instance not found");

        $this->instance = $instance;
    }

}