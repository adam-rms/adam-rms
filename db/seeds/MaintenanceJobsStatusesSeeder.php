<?php


use Phinx\Seed\AbstractSeed;

class MaintenanceJobsStatusesSeeder extends AbstractSeed
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
        $data = [
            [
                "maintenanceJobsStatuses_id" => 1,
                "instances_id" => null,
                "maintenanceJobsStatuses_name" => "Received",
                "maintenanceJobsStatuses_order" => 1,
                "maintenanceJobsStatuses_deleted" => 0,
                "maintenanceJobsStatuses_showJobInMainList" => 1
            ],
            [
                "maintenanceJobsStatuses_id" => 2,
                "instances_id" => null,
                "maintenanceJobsStatuses_name" => "Closed",
                "maintenanceJobsStatuses_order" => 99,
                "maintenanceJobsStatuses_deleted" => 0,
                "maintenanceJobsStatuses_showJobInMainList" => 0
            ]
        ];

        $count = $this->fetchRow('SELECT COUNT(*) AS count FROM maintenanceJobsStatuses');
        if ($count['count'] > 0) {
            return;
        }

        $table = $this->table('maintenanceJobsStatuses');
        $table->insert($data)
            ->saveData();
    }
}
