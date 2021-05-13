<?php


use Phinx\Seed\AbstractSeed;

class PositionsSeeder extends AbstractSeed
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
        $positionGroups = [
            [
                "positionsGroups_id"=> 1,
                "positionsGroups_name"=> "Administrator",
                "positionsGroups_actions"=> "1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22"
            ],
            [
                "positionsGroups_id"=> 999,
                "positionsGroups_name"=> "User",
                "positionsGroups_actions"=> ",8"
            ]
        ];

        $positions = [
            [
                "positions_id"=> 1,
                "positions_displayName"=> "Super-admin",
                "positions_positionsGroups"=> "1",
                "positions_rank"=> 1
            ],
            [
                 "positions_id"=> 999,
                 "positions_displayName"=> "User",
                 "positions_positionsGroups"=> "999",
                 "positions_rank"=> 99
            ]
        ];

        $table = $this->table('positionsGroups');
        $table->insert($positionGroups)
            ->saveData();
        $table = $this->table('positions');
        $table->insert($positions)
            ->saveData();
    }
}
