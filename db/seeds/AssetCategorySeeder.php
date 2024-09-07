<?php


use Phinx\Seed\AbstractSeed;

class AssetCategorySeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {

        $categoryGroupData = [
            [
                "assetCategoriesGroups_id" => 1,
                "assetCategoriesGroups_name" => "Lighting",
                "assetCategoriesGroups_fontAwesome" => "far fa-lightbulb",
                "assetCategoriesGroups_order" => 1
            ],
            [
                "assetCategoriesGroups_id" => 2,
                "assetCategoriesGroups_name" => "Sound",
                "assetCategoriesGroups_fontAwesome" => "fas fa-volume-up",
                "assetCategoriesGroups_order" => 2
            ],
            [
                "assetCategoriesGroups_id" => 3,
                "assetCategoriesGroups_name" => "Video",
                "assetCategoriesGroups_fontAwesome" => "fas fa-tv",
                "assetCategoriesGroups_order" => 3
            ],
            [
                "assetCategoriesGroups_id" => 4,
                "assetCategoriesGroups_name" => "Rigging",
                "assetCategoriesGroups_fontAwesome" => "fas fa-balance-scale-left",
                "assetCategoriesGroups_order" => 4
            ],
            [
                "assetCategoriesGroups_id" => 5,
                "assetCategoriesGroups_name" => "Computers & Networks",
                "assetCategoriesGroups_fontAwesome" => "fas fa-server",
                "assetCategoriesGroups_order" => 6
            ],
            [
                "assetCategoriesGroups_id" => 6,
                "assetCategoriesGroups_name" => "Communication",
                "assetCategoriesGroups_fontAwesome" => "fas fa-headset",
                "assetCategoriesGroups_order" => 5
            ],
            [
                "assetCategoriesGroups_id" => 10,
                "assetCategoriesGroups_name" => "Costume",
                "assetCategoriesGroups_fontAwesome" => null,
                "assetCategoriesGroups_order" => 10
            ],
            [
                "assetCategoriesGroups_id" => 11,
                "assetCategoriesGroups_name" => "Props",
                "assetCategoriesGroups_fontAwesome" => null,
                "assetCategoriesGroups_order" => 11
            ],
            [
                "assetCategoriesGroups_id" => 12,
                "assetCategoriesGroups_name" => "Scenery",
                "assetCategoriesGroups_fontAwesome" => null,
                "assetCategoriesGroups_order" => 12
            ],
            [
                "assetCategoriesGroups_id" => 999,
                "assetCategoriesGroups_name" => "Miscellaneous",
                "assetCategoriesGroups_fontAwesome" => "fas fa-question",
                "assetCategoriesGroups_order" => 999
            ]
        ];
        $categoryData = [
            [
                "assetCategories_id" => 1,
                "assetCategories_name" => "Conventionals",
                "assetCategories_fontAwesome" => "far fa-lightbulb",
                "assetCategories_rank" => 11,
                "assetCategoriesGroups_id" => 1,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 2,
                "assetCategories_name" => "Moving Lights",
                "assetCategories_fontAwesome" => "fas fa-robot",
                "assetCategories_rank" => 12,
                "assetCategoriesGroups_id" => 1,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 3,
                "assetCategories_name" => "LEDs",
                "assetCategories_fontAwesome" => "fas fa-traffic-light",
                "assetCategories_rank" => 13,
                "assetCategoriesGroups_id" => 1,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 4,
                "assetCategories_name" => "Colour Changers",
                "assetCategories_fontAwesome" => "fas fa-swatchbook",
                "assetCategories_rank" => 14,
                "assetCategoriesGroups_id" => 1,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 5,
                "assetCategories_name" => "Accessories and Effects",
                "assetCategories_fontAwesome" => "fas fa-fire",
                "assetCategories_rank" => 18,
                "assetCategoriesGroups_id" => 1,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 7,
                "assetCategories_name" => "Mixing Desks",
                "assetCategories_fontAwesome" => "fas fa-headphones",
                "assetCategories_rank" => 22,
                "assetCategoriesGroups_id" => 2,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 8,
                "assetCategories_name" => "Amplifiers",
                "assetCategories_fontAwesome" => "fas fa-bullhorn",
                "assetCategories_rank" => 24,
                "assetCategoriesGroups_id" => 2,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 9,
                "assetCategories_name" => "Microphones & DI",
                "assetCategories_fontAwesome" => "fas fa-microphone",
                "assetCategories_rank" => 21,
                "assetCategoriesGroups_id" => 2,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 10,
                "assetCategories_name" => "Accessories",
                "assetCategories_fontAwesome" => "fas fa-headset",
                "assetCategories_rank" => 26,
                "assetCategoriesGroups_id" => 2,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 11,
                "assetCategories_name" => "Speakers",
                "assetCategories_fontAwesome" => "fas fa-volume-up",
                "assetCategories_rank" => 23,
                "assetCategoriesGroups_id" => 2,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 12,
                "assetCategories_name" => "Cables",
                "assetCategories_fontAwesome" => "fas fa-network-wired",
                "assetCategories_rank" => 70,
                "assetCategoriesGroups_id" => 999,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 14,
                "assetCategories_name" => "Rigging",
                "assetCategories_fontAwesome" => "fas fa-balance-scale-left",
                "assetCategories_rank" => 51,
                "assetCategoriesGroups_id" => 4,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 15,
                "assetCategories_name" => "Dimmers",
                "assetCategories_fontAwesome" => "fas fa-bolt",
                "assetCategories_rank" => 17,
                "assetCategoriesGroups_id" => 1,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 16,
                "assetCategories_name" => "Computers",
                "assetCategories_fontAwesome" => "fas fa-desktop",
                "assetCategories_rank" => 40,
                "assetCategoriesGroups_id" => 5,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 17,
                "assetCategories_name" => "Drapes, Curtains & Cloths",
                "assetCategories_fontAwesome" => "far fa-eye-slash",
                "assetCategories_rank" => 53,
                "assetCategoriesGroups_id" => 4,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 19,
                "assetCategories_name" => "Accessories",
                "assetCategories_fontAwesome" => "fas fa-video",
                "assetCategories_rank" => 33,
                "assetCategoriesGroups_id" => 3,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 20,
                "assetCategories_name" => "Outboard",
                "assetCategories_fontAwesome" => "fas fa-assistive-listening-systems",
                "assetCategories_rank" => 25,
                "assetCategoriesGroups_id" => 2,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 21,
                "assetCategories_name" => "Vision Mixers and Media Servers",
                "assetCategories_fontAwesome" => "fas fa-server",
                "assetCategories_rank" => 32,
                "assetCategoriesGroups_id" => 3,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 22,
                "assetCategories_name" => "Control",
                "assetCategories_fontAwesome" => "fas fa-microchip",
                "assetCategories_rank" => 15,
                "assetCategoriesGroups_id" => 1,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 23,
                "assetCategories_name" => "Cases, Boxes and Trolleys",
                "assetCategories_fontAwesome" => "fas fa-truck-loading",
                "assetCategories_rank" => 60,
                "assetCategoriesGroups_id" => 999,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 24,
                "assetCategories_name" => "Tools, Safety & Access",
                "assetCategories_fontAwesome" => "fas fa-wrench",
                "assetCategories_rank" => 52,
                "assetCategoriesGroups_id" => 4,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 25,
                "assetCategories_name" => "Displays, Panels & Projectors",
                "assetCategories_fontAwesome" => "fas fa-tv",
                "assetCategories_rank" => 30,
                "assetCategoriesGroups_id" => 3,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 26,
                "assetCategories_name" => "Accessories",
                "assetCategories_fontAwesome" => "far fa-keyboard",
                "assetCategories_rank" => 41,
                "assetCategoriesGroups_id" => 5,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 27,
                "assetCategories_name" => "Radios",
                "assetCategories_fontAwesome" => "fas fa-satellite-dish",
                "assetCategories_rank" => 27,
                "assetCategoriesGroups_id" => 6,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 28,
                "assetCategories_name" => "Networking",
                "assetCategories_fontAwesome" => "fas fa-ethernet",
                "assetCategories_rank" => 42,
                "assetCategoriesGroups_id" => 5,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 29,
                "assetCategories_name" => "Mains Distribution",
                "assetCategories_fontAwesome" => "fas fa-plug",
                "assetCategories_rank" => 81,
                "assetCategoriesGroups_id" => 999,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 30,
                "assetCategories_name" => "Systems",
                "assetCategories_fontAwesome" => "fas fa-headset",
                "assetCategories_rank" => 999,
                "assetCategoriesGroups_id" => 6,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 31,
                "assetCategories_name" => "Cameras",
                "assetCategories_fontAwesome" => "fas fa-camera",
                "assetCategories_rank" => 31,
                "assetCategoriesGroups_id" => 3,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ],
            [
                "assetCategories_id" => 33,
                "assetCategories_name" => "Tablets & Mobile Phones",
                "assetCategories_fontAwesome" => "fas fa-mobile-alt",
                "assetCategories_rank" => 40,
                "assetCategoriesGroups_id" => 5,
                "instances_id" => null,
                "assetCategories_deleted" => 0
            ]
        ];

        $count = $this->fetchRow('SELECT COUNT(*) AS count FROM assetCategoriesGroups');
        if ($count['count'] > 0) {
            return;
        }
        $count = $this->fetchRow('SELECT COUNT(*) AS count FROM assetCategories');
        if ($count['count'] > 0) {
            return;
        }

        $table = $this->table('assetCategoriesGroups');
        $table->insert($categoryGroupData)
            ->saveData();
        $table = $this->table('assetCategories');
        $table->insert($categoryData)
            ->saveData();
    }
}
