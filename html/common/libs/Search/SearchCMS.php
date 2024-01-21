<?php

require_once __DIR__ . '/BaseSearch.php';

class SearchCMS extends BaseSearch
{
    protected $TWIG;

    public function __construct(?int $instance_id = null)
    {
        parent::__construct($instance_id);
        global $TWIG;
        $this->TWIG = $TWIG;
    }

    private function html2text($html)
    {
        $text = trim(preg_replace('/\s\s+/', ' ', preg_replace("/\n\s+/", "\n", rtrim(html_entity_decode(strip_tags(str_replace(["<br/>", "<br>"], "\n", $html)))))));
        return $text;
    }
    /**
     * Load the pages that are able to be searched from the database
     * @return array An array of objects in the correct format
     * @throws Exception
     */
    public function load(): array
    {
        parent::load();

        /**
         * For the following sub query to get the content (cmsPagesDrafts_data) from the most recent commit for each page
         *
         *     SELECT a.cmsPagesDrafts_data, a.cmsPages_id FROM cmsPagesDrafts a LEFT OUTER JOIN cmsPagesDrafts b
         *          ON a.cmsPages_id = b.cmsPages_id AND a.cmsPagesDrafts_revisionID < b.cmsPagesDrafts_revisionID
         *     WHERE b.cmsPages_id IS NULL
         *
         */
        $subQuery = $this->DBLIB->subQuery("content");
        $subQuery->join("cmsPagesDrafts b", "a.cmsPages_id = b.cmsPages_id AND a.cmsPagesDrafts_revisionID < b.cmsPagesDrafts_revisionID", "LEFT OUTER");
        $subQuery->where("b.cmsPages_id IS NULL");
        $subQuery->get("cmsPagesDrafts a", null, "a.cmsPagesDrafts_data, a.cmsPages_id");


        //Get page, combined with the sub query so we have the page and the content in the same database call
        $this->DBLIB->where("instances_id", $this->instance['instances_id']);
        $this->DBLIB->where("cmsPages_deleted", 0);
        $this->DBLIB->where("cmsPages_archived", 0);
        $this->DBLIB->join($subQuery, "page.cmsPages_id = content.cmsPages_id");
        if ($this->AUTH->data['instance']["instancePositions_id"]) $this->DBLIB->where("(cmsPages_visibleToGroups IS NULL OR (FIND_IN_SET(  ? , cmsPages_visibleToGroups) > 0))", array($this->AUTH->data['instance']["instancePositions_id"])); //If the user doesn't have a position - they're server admins

        $output = [];

        // get the data and iterate through each page
        foreach ($this->DBLIB->get("cmsPages page", null, "page.cmsPages_id as cmsPages_id, cmsPages_name, cmsPages_description, cmsPagesDrafts_data") as $page) {
            // Generate the content
            $render = $this->TWIG->render('assets/templates/cmsPage.twig', ['pageData' => [
                "DRAFTS" => [
                    "cmsPagesDrafts_dataARRAY" => json_decode($page['cmsPagesDrafts_data'], true)
                ]
            ]]);

            // Convert the content text, and then remove any new lines
            $page_text = $this->html2text($render);

            // Create the data structure for the item
            $pageData = [
                'type' => "page",
                'searchable' => [
                    $page["cmsPages_name"],
                    $page["cmsPages_description"],
                    $page_text
                ],
                'title' => $page["cmsPages_name"],
                'except' => mb_strimwidth($page_text, 0, 200, "..."),
                'data' => array_merge(['render' => $render], $page)
            ];
            array_push($output, $pageData);
        }

        return $output;
    }
}
