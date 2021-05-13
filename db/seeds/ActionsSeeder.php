<?php


use Phinx\Seed\AbstractSeed;

class ActionsSeeder extends AbstractSeed
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
                "actionsCategories_id"=> 1,
                "actionsCategories_name"=> "User Management",
                "actionsCategories_order"=> 0
            ],
            [
                "actionsCategories_id"=> 2,
                "actionsCategories_name"=> "Permissions Management",
                "actionsCategories_order"=> 1
            ],
            [
                "actionsCategories_id"=> 3,
                "actionsCategories_name"=> "General sys admin",
                "actionsCategories_order"=> 2
            ],
            [
                "actionsCategories_id"=> 4,
                "actionsCategories_name"=> "Instances",
                "actionsCategories_order"=> 3
            ],
            [
                "actionsCategories_id"=> 5,
                "actionsCategories_name"=> "Assets",
                "actionsCategories_order"=> 4
            ]
        ];
        $actionData = [
          [
              "actions_id"=> 2,
            "actions_name"=> "Access a list of users",
            "actionsCategories_id"=> 1,
            "actions_dependent"=> null,
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 4,
            "actions_name"=> "Create a new user",
            "actionsCategories_id"=> 1,
            "actions_dependent"=> "2",
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 5,
            "actions_name"=> "Edit details about a user",
            "actionsCategories_id"=> 1,
            "actions_dependent"=> "2,4",
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 6,
            "actions_name"=> "View mailing for a user",
            "actionsCategories_id"=> 1,
            "actions_dependent"=> "2",
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 7,
            "actions_name"=> "View audit log",
            "actionsCategories_id"=> 3,
            "actions_dependent"=> null,
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 8,
            "actions_name"=> "Create a new instance",
            "actionsCategories_id"=> 4,
            "actions_dependent"=> "",
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 9,
            "actions_name"=> "Suspend a user",
            "actionsCategories_id"=> 1,
            "actions_dependent"=> "2",
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 10,
            "actions_name"=> "View site as a user",
            "actionsCategories_id"=> 1,
            "actions_dependent"=> "2,3,5,6,9",
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 11,
            "actions_name"=> "Access a list of permissions",
            "actionsCategories_id"=> 2,
            "actions_dependent"=> null,
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 12,
            "actions_name"=> "Edit list of permissions",
            "actionsCategories_id"=> 2,
            "actions_dependent"=> null,
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 13,
            "actions_name"=> "Change a user's permissions",
            "actionsCategories_id"=> 2,
            "actions_dependent"=> "5,16",
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 14,
            "actions_name"=> "Set a user's thumbnail",
            "actionsCategories_id"=> 1,
            "actions_dependent"=> "5",
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 15,
            "actions_name"=> "Delete a user",
            "actionsCategories_id"=> 1,
            "actions_dependent"=> "2",
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 16,
            "actions_name"=> "View own positions",
            "actionsCategories_id"=> 2,
            "actions_dependent"=> "",
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 17,
            "actions_name"=> "Use the Development Site",
            "actionsCategories_id"=> 4,
            "actions_dependent"=> "",
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 18,
            "actions_name"=> "View PHP Info",
            "actionsCategories_id"=> 4,
            "actions_dependent"=> null,
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 19,
            "actions_name"=> "Edit any asset type - even those written by AdamRMS",
            "actionsCategories_id"=> 5,
            "actions_dependent"=> null,
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 20,
            "actions_name"=> "Access a list of instances",
            "actionsCategories_id"=> 4,
            "actions_dependent"=> null,
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 21,
            "actions_name"=> "Log in to any instance with full permissions",
            "actionsCategories_id"=> 4,
            "actions_dependent"=> "20",
            "actions_incompatible"=> null
          ],
          [
              "actions_id"=> 22,
            "actions_name"=> "Change another users notification settings",
            "actionsCategories_id"=> 1,
            "actions_dependent"=> "5",
            "actions_incompatible"=> null
          ]
        ];

        $table = $this->table('actionscategories');
        $table->truncate();
        $table->insert($categoryData)
            ->saveData();
        $table = $this->table('actions');
        $table->truncate();
        $table->insert($actionData)
            ->saveData();
    }
}
