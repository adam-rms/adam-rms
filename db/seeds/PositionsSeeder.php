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
        require_once __DIR__ . '/../../html/common/libs/Auth/serverActions.php';
        $positionGroups = [
            [
                "positionsGroups_id"=> 1,
                "positionsGroups_name"=> "Administrator",
                "positionsGroups_actions"=> implode(",",array_keys($serverActions))
            ],
            [
                "positionsGroups_id"=> 2,
                "positionsGroups_name"=> "User",
                "positionsGroups_actions"=> []
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
                "positions_id"=> 2,
                "positions_displayName"=> "User",
                "positions_positionsGroups"=> "2",
                "positions_rank"=> 2
            ],
        ];

        $this->execute("DELETE FROM instances");
        $this->execute("DELETE FROM positions");
        $this->execute("DELETE FROM positionsGroups");

        $positionsTable = $this->table('positions');
        $positionsGroupsTable = $this->table('positionsGroups');

        $positionsGroupsTable->insert($positionGroups)
            ->saveData();
        $positionsTable->insert($positions)
            ->saveData();
    }
}
