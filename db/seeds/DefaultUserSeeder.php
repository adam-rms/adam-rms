<?php


use Phinx\Seed\AbstractSeed;

class DefaultUserSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function getDependencies(): array
    {
        return [
            'PositionsSeeder'
        ];
    }
    public function run(): void
    {
        $data = [
            [
                "users_username" => "username",
                "users_name1" => "UserF",
                "users_name2" => "UserL",
                "users_userid" => 1,
                "users_salty1" => "8smqAFD9",
                "users_password" => "fa5a51baef12914c7f2e0e1176a030bf086d26edae298c25d5f84c90bc72ecd7",
                "users_salty2" => "uOhfrOCW",
                "users_hash" => "sha256",
                "users_email" => "test@example.com",
                "users_created" => date('Y-m-d H:i:s'),
                "users_changepass" => 0,
                "users_suspended" => 0,
                "users_deleted" => 0,
                "users_emailVerified" => 1
            ]
        ];

        $assign = [
            [
                "userPositions_id" => 1,
                "users_userid" => 1,
                "userPositions_start" => date('Y-m-d H:i:s'),
                "userPositions_end" => null,
                "positions_id" => 1,
                "userPositions_displayName" => null,
                "userPositions_extraPermissions" => null,
                "userPositions_show" => 1
            ]
        ];

        $count = $this->fetchRow('SELECT COUNT(*) AS count FROM users');
        if ($count['count'] > 0) {
            return;
        }
        $count = $this->fetchRow('SELECT COUNT(*) AS count FROM userPositions');
        if ($count['count'] > 0) {
            return;
        }

        $user = $this->table('users');
        $user->insert($data)
            ->saveData();
        $assignTable = $this->table('userPositions');
        $assignTable->insert($assign)
            ->saveData();
    }
}
