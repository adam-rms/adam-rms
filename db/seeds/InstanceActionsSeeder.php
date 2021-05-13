<?php


use Phinx\Seed\AbstractSeed;

class InstanceActionsSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run()
    {
        $categoryData = [
            [
                "instanceActionsCategories_id"=> 1,
                "instanceActionsCategories_name"=> "User Management",
                "instanceActionsCategories_order"=> 110
            ],
            [
                "instanceActionsCategories_id"=> 2,
                "instanceActionsCategories_name"=> "Permissions Management",
                "instanceActionsCategories_order"=> 120
            ],
            [
                "instanceActionsCategories_id"=> 3,
                "instanceActionsCategories_name"=> "Assets",
                "instanceActionsCategories_order"=> 10
            ],
            [
                "instanceActionsCategories_id"=> 4,
                "instanceActionsCategories_name"=> "Projects",
                "instanceActionsCategories_order"=> 30
            ],
            [
                "instanceActionsCategories_id"=> 5,
                "instanceActionsCategories_name"=> "Finance",
                "instanceActionsCategories_order"=> 40
            ],
            [
                "instanceActionsCategories_id"=> 6,
                "instanceActionsCategories_name"=> "Clients",
                "instanceActionsCategories_order"=> 20
            ],
            [
                "instanceActionsCategories_id"=> 7,
                "instanceActionsCategories_name"=> "Files",
                "instanceActionsCategories_order"=> 50
            ],
            [
                "instanceActionsCategories_id"=> 8,
                "instanceActionsCategories_name"=> "Maintenance",
                "instanceActionsCategories_order"=> 60
            ],
            [
                "instanceActionsCategories_id"=> 9,
                "instanceActionsCategories_name"=> "Business",
                "instanceActionsCategories_order"=> 70
            ],
            [
                "instanceActionsCategories_id"=> 10,
                "instanceActionsCategories_name"=> "App",
                "instanceActionsCategories_order"=> 100
            ],
            [
                "instanceActionsCategories_id"=> 11,
                "instanceActionsCategories_name"=> "Locations",
                "instanceActionsCategories_order"=> 80
            ],
            [
                "instanceActionsCategories_id"=> 12,
                "instanceActionsCategories_name"=> "Groups",
                "instanceActionsCategories_order"=> 90
            ],
            [
                "instanceActionsCategories_id"=> 13,
                "instanceActionsCategories_name"=> "Project Types",
                "instanceActionsCategories_order"=> 31
            ],
            [
                "instanceActionsCategories_id"=> 14,
                "instanceActionsCategories_name"=> "Training",
                "instanceActionsCategories_order"=> 75
            ],
            [
                "instanceActionsCategories_id"=> 15,
                "instanceActionsCategories_name"=> "CMS",
                "instanceActionsCategories_order"=> 71
            ]
        ];
        $actionData = [
            [
                "instanceActions_id"=> 2,
                "instanceActions_name"=> "Access a list of users",
                "instanceActionsCategories_id"=> 1,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 3,
                "instanceActions_name"=> "Add a user to a business by EMail",
                "instanceActionsCategories_id"=> 1,
                "instanceActions_dependent"=> "2\r\n",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 5,
                "instanceActions_name"=> "Remove a user from a business",
                "instanceActionsCategories_id"=> 1,
                "instanceActions_dependent"=> "2",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 6,
                "instanceActions_name"=> "Change the role of a user in a business",
                "instanceActionsCategories_id"=> 1,
                "instanceActions_dependent"=> "2",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 11,
                "instanceActions_name"=> "Access list of roles and their permissions",
                "instanceActionsCategories_id"=> 2,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 12,
                "instanceActions_name"=> "Edit roles permissions [SUPER ADMIN]",
                "instanceActionsCategories_id"=> 2,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 13,
                "instanceActions_name"=> "Change a user's permissions",
                "instanceActionsCategories_id"=> 2,
                "instanceActions_dependent"=> "5",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 14,
                "instanceActions_name"=> "Set a user's thumbnail",
                "instanceActionsCategories_id"=> 1,
                "instanceActions_dependent"=> "5",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 15,
                "instanceActions_name"=> "Delete a user",
                "instanceActionsCategories_id"=> 1,
                "instanceActions_dependent"=> "2",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 16,
                "instanceActions_name"=> "Add new roles",
                "instanceActionsCategories_id"=> 2,
                "instanceActions_dependent"=> "11",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 17,
                "instanceActions_name"=> "Create new Asset",
                "instanceActionsCategories_id"=> 3,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 18,
                "instanceActions_name"=> "Create new Asset Type",
                "instanceActionsCategories_id"=> 3,
                "instanceActions_dependent"=> "17",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 19,
                "instanceActions_name"=> "Delete Asset",
                "instanceActionsCategories_id"=> 3,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 20,
                "instanceActions_name"=> "View Projects",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 21,
                "instanceActions_name"=> "Create Project",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> "20,104,23",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 22,
                "instanceActions_name"=> "Change Project Client",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> "20",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 23,
                "instanceActions_name"=> "Change Project Lead",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> "20",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 24,
                "instanceActions_name"=> "Change Project Description",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> "20",
                "instanceActions_incompatible"=> ""
            ],
            [
                "instanceActions_id"=> 25,
                "instanceActions_name"=> "Archive Project",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> "20",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 26,
                "instanceActions_name"=> "Delete Project",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> "20",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 27,
                "instanceActions_name"=> "Change Project Dates",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> "20",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 28,
                "instanceActions_name"=> "Change Project Name",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> "20",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 29,
                "instanceActions_name"=> "Change Project Status",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> "20",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 30,
                "instanceActions_name"=> "Change Project Address",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> "20",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 31,
                "instanceActions_name"=> "Assign/unassign asset to Project",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> "20",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 32,
                "instanceActions_name"=> "Assign all assets to Project",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> "31",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 33,
                "instanceActions_name"=> "View Project Payments",
                "instanceActionsCategories_id"=> 5,
                "instanceActions_dependent"=> "20",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 34,
                "instanceActions_name"=> "Add new Project Payment",
                "instanceActionsCategories_id"=> 5,
                "instanceActions_dependent"=> "33",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 35,
                "instanceActions_name"=> "Delete a Project Payment",
                "instanceActionsCategories_id"=> 5,
                "instanceActions_dependent"=> "33",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 36,
                "instanceActions_name"=> "View Clients List",
                "instanceActionsCategories_id"=> 6,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 37,
                "instanceActions_name"=> "Add new Client",
                "instanceActionsCategories_id"=> 6,
                "instanceActions_dependent"=> "36\r\n",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 38,
                "instanceActions_name"=> "Add new Manufacturer",
                "instanceActionsCategories_id"=> 3,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 39,
                "instanceActions_name"=> "Edit Client",
                "instanceActionsCategories_id"=> 6,
                "instanceActions_dependent"=> "36",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 40,
                "instanceActions_name"=> "View Ledger",
                "instanceActionsCategories_id"=> 5,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 41,
                "instanceActions_name"=> "Edit asset assignment comment",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 42,
                "instanceActions_name"=> "Edit asset assignment custom price",
                "instanceActionsCategories_id"=> 5,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 43,
                "instanceActions_name"=> "Edit asset assignment discount",
                "instanceActionsCategories_id"=> 5,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 44,
                "instanceActions_name"=> "Edit Project Notes",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> "20",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 45,
                "instanceActions_name"=> "Add Project Notes",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> "44",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 46,
                "instanceActions_name"=> "Change Project Invoice Notes",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> "20",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 47,
                "instanceActions_name"=> "View Project Crew",
                "instanceActionsCategories_id"=> 5,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 48,
                "instanceActions_name"=> "Add Crew to Project",
                "instanceActionsCategories_id"=> 5,
                "instanceActions_dependent"=> "47",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 49,
                "instanceActions_name"=> "Delete Crew Assignment",
                "instanceActionsCategories_id"=> 5,
                "instanceActions_dependent"=> "47",
                "instanceActions_incompatible"=> ""
            ],
            [
                "instanceActions_id"=> 50,
                "instanceActions_name"=> "Email Project Crew",
                "instanceActionsCategories_id"=> 5,
                "instanceActions_dependent"=> "47",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 51,
                "instanceActions_name"=> "Edit Crew Ranks",
                "instanceActionsCategories_id"=> 5,
                "instanceActions_dependent"=> "47",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 52,
                "instanceActions_name"=> "View User details",
                "instanceActionsCategories_id"=> 1,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 53,
                "instanceActions_name"=> "Change the assignment status for an asset (e.g. mark as packed)",
                "instanceActionsCategories_id"=> 5,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 54,
                "instanceActions_name"=> "View Asset Type File Attachments",
                "instanceActionsCategories_id"=> 7,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 55,
                "instanceActions_name"=> "Upload Asset Type File Attachments",
                "instanceActionsCategories_id"=> 7,
                "instanceActions_dependent"=> "54",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 56,
                "instanceActions_name"=> "Re-name a file",
                "instanceActionsCategories_id"=> 7,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 57,
                "instanceActions_name"=> "Delete a file",
                "instanceActionsCategories_id"=> 7,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 58,
                "instanceActions_name"=> "Edit Asset Type",
                "instanceActionsCategories_id"=> 3,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 59,
                "instanceActions_name"=> "Edit Asset",
                "instanceActionsCategories_id"=> 3,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 61,
                "instanceActions_name"=> "View Asset File Attachments",
                "instanceActionsCategories_id"=> 7,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 62,
                "instanceActions_name"=> "Upload Asset File Attachments",
                "instanceActionsCategories_id"=> 7,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 63,
                "instanceActions_name"=> "Access Maintenance",
                "instanceActionsCategories_id"=> 8,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 67,
                "instanceActions_name"=> "Change job due date",
                "instanceActionsCategories_id"=> 8,
                "instanceActions_dependent"=> "63",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 68,
                "instanceActions_name"=> "Change user assigned to job",
                "instanceActionsCategories_id"=> 8,
                "instanceActions_dependent"=> "63",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 69,
                "instanceActions_name"=> "Edit users tagged in job",
                "instanceActionsCategories_id"=> 8,
                "instanceActions_dependent"=> "63",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 70,
                "instanceActions_name"=> "Edit Job Name",
                "instanceActionsCategories_id"=> 8,
                "instanceActions_dependent"=> "63",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 71,
                "instanceActions_name"=> "Add message to job",
                "instanceActionsCategories_id"=> 8,
                "instanceActions_dependent"=> "63",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 72,
                "instanceActions_name"=> "Delete Job\r\n",
                "instanceActionsCategories_id"=> 8,
                "instanceActions_dependent"=> "63",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 73,
                "instanceActions_name"=> "Change job status",
                "instanceActionsCategories_id"=> 8,
                "instanceActions_dependent"=> "63",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 74,
                "instanceActions_name"=> "Add Assets to Job",
                "instanceActionsCategories_id"=> 8,
                "instanceActions_dependent"=> "63",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 75,
                "instanceActions_name"=> "Remove Assets from Job",
                "instanceActionsCategories_id"=> 8,
                "instanceActions_dependent"=> "63",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 76,
                "instanceActions_name"=> "Upload files to Job",
                "instanceActionsCategories_id"=> 8,
                "instanceActions_dependent"=> "63,71",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 77,
                "instanceActions_name"=> "Change job priority",
                "instanceActionsCategories_id"=> 8,
                "instanceActions_dependent"=> "63",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 78,
                "instanceActions_name"=> "Make job flag against assets",
                "instanceActionsCategories_id"=> 8,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 79,
                "instanceActions_name"=> "Make job block asset assignments",
                "instanceActionsCategories_id"=> 8,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 80,
                "instanceActions_name"=> "View business stats",
                "instanceActionsCategories_id"=> 9,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 81,
                "instanceActions_name"=> "View business settings page",
                "instanceActionsCategories_id"=> 9,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 82,
                "instanceActions_name"=> "Edit Asset Overrides",
                "instanceActionsCategories_id"=> 3,
                "instanceActions_dependent"=> "59",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 83,
                "instanceActions_name"=> "Edit business settings",
                "instanceActionsCategories_id"=> 9,
                "instanceActions_dependent"=> "81",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 84,
                "instanceActions_name"=> "View Asset Barcodes",
                "instanceActionsCategories_id"=> 3,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 85,
                "instanceActions_name"=> "Scan Barcodes in the App",
                "instanceActionsCategories_id"=> 10,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 86,
                "instanceActions_name"=> "Delete Asset Barcodes",
                "instanceActionsCategories_id"=> 3,
                "instanceActions_dependent"=> "84",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 87,
                "instanceActions_name"=> "View a list of locations",
                "instanceActionsCategories_id"=> 11,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 88,
                "instanceActions_name"=> "Associate any unassociated barcode with an asset",
                "instanceActionsCategories_id"=> 10,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 89,
                "instanceActions_name"=> "View a list of custom categories",
                "instanceActionsCategories_id"=> 9,
                "instanceActions_dependent"=> "",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 90,
                "instanceActions_name"=> "Add a new custom category",
                "instanceActionsCategories_id"=> 9,
                "instanceActions_dependent"=> "89,91",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 91,
                "instanceActions_name"=> "Edit a custom category",
                "instanceActionsCategories_id"=> 9,
                "instanceActions_dependent"=> "89",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 92,
                "instanceActions_name"=> "Delete a custom category",
                "instanceActionsCategories_id"=> 9,
                "instanceActions_dependent"=> "89",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 93,
                "instanceActions_name"=> "Create new Group",
                "instanceActionsCategories_id"=> 12,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 94,
                "instanceActions_name"=> "Edit an existing Group",
                "instanceActionsCategories_id"=> 12,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 95,
                "instanceActions_name"=> "Delete a Group",
                "instanceActionsCategories_id"=> 12,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 96,
                "instanceActions_name"=> "Add/Remove group members",
                "instanceActionsCategories_id"=> 12,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 97,
                "instanceActions_name"=> "Archive Asset",
                "instanceActionsCategories_id"=> 3,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 98,
                "instanceActions_name"=> "Add a new location",
                "instanceActionsCategories_id"=> 11,
                "instanceActions_dependent"=> "87",
                "instanceActions_incompatible"=> ""
            ],
            [
                "instanceActions_id"=> 99,
                "instanceActions_name"=> "Edit a location",
                "instanceActionsCategories_id"=> 11,
                "instanceActions_dependent"=> "87",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 100,
                "instanceActions_name"=> "View Location File Attachments",
                "instanceActionsCategories_id"=> 7,
                "instanceActions_dependent"=> "87",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 101,
                "instanceActions_name"=> "Upload Location File Attachments",
                "instanceActionsCategories_id"=> 7,
                "instanceActions_dependent"=> "100",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 102,
                "instanceActions_name"=> "Upload Project Files",
                "instanceActionsCategories_id"=> 7,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 103,
                "instanceActions_name"=> "View location barcodes",
                "instanceActionsCategories_id"=> 11,
                "instanceActions_dependent"=> "87",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 104,
                "instanceActions_name"=> "Change Project Type",
                "instanceActionsCategories_id"=> 13,
                "instanceActions_dependent"=> "",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 105,
                "instanceActions_name"=> "View list of Project Types",
                "instanceActionsCategories_id"=> 13,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 106,
                "instanceActions_name"=> "Add new Project Type",
                "instanceActionsCategories_id"=> 13,
                "instanceActions_dependent"=> "105",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 107,
                "instanceActions_name"=> "Edit Project Type",
                "instanceActionsCategories_id"=> 13,
                "instanceActions_dependent"=> "105",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 108,
                "instanceActions_name"=> "Delete Project Type",
                "instanceActionsCategories_id"=> 13,
                "instanceActions_dependent"=> "107,105",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 109,
                "instanceActions_name"=> "View list of Signup Codes",
                "instanceActionsCategories_id"=> 1,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 110,
                "instanceActions_name"=> "Add new Signup Code",
                "instanceActionsCategories_id"=> 1,
                "instanceActions_dependent"=> "109",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 111,
                "instanceActions_name"=> "Edit Signup Code",
                "instanceActionsCategories_id"=> 1,
                "instanceActions_dependent"=> "109",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 112,
                "instanceActions_name"=> "Delete Signup Code",
                "instanceActionsCategories_id"=> 1,
                "instanceActions_dependent"=> "109,112",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 113,
                "instanceActions_name"=> "Access Training",
                "instanceActionsCategories_id"=> 14,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 114,
                "instanceActions_name"=> "View draft training modules",
                "instanceActionsCategories_id"=> 14,
                "instanceActions_dependent"=> "113",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 115,
                "instanceActions_name"=> "Add Training module",
                "instanceActionsCategories_id"=> 14,
                "instanceActions_dependent"=> "116",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 116,
                "instanceActions_name"=> "Edit Training modules",
                "instanceActionsCategories_id"=> 14,
                "instanceActions_dependent"=> "114",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 117,
                "instanceActions_name"=> "View a list of users that have completed a training module",
                "instanceActionsCategories_id"=> 14,
                "instanceActions_dependent"=> "113,2",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 118,
                "instanceActions_name"=> "Archive a user",
                "instanceActionsCategories_id"=> 1,
                "instanceActions_dependent"=> "2",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 119,
                "instanceActions_name"=> "Certify a user's training",
                "instanceActionsCategories_id"=> 14,
                "instanceActions_dependent"=> "117",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 120,
                "instanceActions_name"=> "Revoke a user's training",
                "instanceActionsCategories_id"=> 14,
                "instanceActions_dependent"=> "119",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 121,
                "instanceActions_name"=> "View Payment File Attachments",
                "instanceActionsCategories_id"=> 5,
                "instanceActions_dependent"=> "40",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 122,
                "instanceActions_name"=> "Upload Payment File Attachments",
                "instanceActionsCategories_id"=> 5,
                "instanceActions_dependent"=> "40",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 123,
                "instanceActions_name"=> "Manage Crew Recruitment for a Project",
                "instanceActionsCategories_id"=> 5,
                "instanceActions_dependent"=> "47",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 124,
                "instanceActions_name"=> "View & Apply for Crew Roles",
                "instanceActionsCategories_id"=> 4,
                "instanceActions_dependent"=> "20",
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 125,
                "instanceActions_name"=> "Manage CMS Pages",
                "instanceActionsCategories_id"=> 15,
                "instanceActions_dependent"=> null,
                "instanceActions_incompatible"=> null
            ],
            [
                "instanceActions_id"=> 126,
                "instanceActions_name"=> "Edit any CMS Pages",
                "instanceActionsCategories_id"=> 15,
                "instanceActions_dependent"=> "125",
                "instanceActions_incompatible"=> null
            ]
        ];

        $table = $this->table('instanceActionsCategories');
        $table->insert($categoryData)
            ->saveData();
        $table = $this->table('instanceActions');
        $table->insert($actionData)
            ->saveData();
    }
}
