<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class CustomProjectStatus extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $originalStatuses = [
            0 => [
                "projectsStatuses_name" => "Added to RMS",
                "projectsStatuses_description" => "Default",
                "projectsStatuses_foregroundColour" => "#000000",
                "projectsStatuses_backgroundColour" => "#F5F5F5",
                "projectsStatuses_rank" => 0,
                "projectsStatuses_assetsReleased" => false,
                "projectsStatuses_class" => "info"
            ],
            1 => [
                "projectsStatuses_name" => "Targeted",
                "projectsStatuses_description" => "Being targeted as a lead",
                "projectsStatuses_foregroundColour" => "#000000",
                "projectsStatuses_backgroundColour" => "#F5F5F5",
                "projectsStatuses_rank" => 1,
                "projectsStatuses_assetsReleased" => false,
                "projectsStatuses_class" => "info"
            ],
            2 => [
                "projectsStatuses_name" => "Quote Sent",
                "projectsStatuses_description" => "Waiting for client confirmation",
                "projectsStatuses_foregroundColour" => "#000000",
                "projectsStatuses_backgroundColour" => "#ffdd99",
                "projectsStatuses_rank" => 2,
                "projectsStatuses_assetsReleased" => false,
                "projectsStatuses_class" => "warning"
            ],
            3 => [
                "projectsStatuses_name" => "Confirmed",
                "projectsStatuses_description" => "Booked in with client",
                "projectsStatuses_foregroundColour" => "#ffffff",
                "projectsStatuses_backgroundColour" => "#66ff66",
                "projectsStatuses_rank" => 3,
                "projectsStatuses_assetsReleased" => false,
                "projectsStatuses_class" => "success"
            ],
            4 => [
                "projectsStatuses_name" => "Prep",
                "projectsStatuses_description" => "Being prepared for dispatch" ,
                "projectsStatuses_foregroundColour" => "#000000",
                "projectsStatuses_backgroundColour" => "#ffdd99",
                "projectsStatuses_rank" => 4,
                "projectsStatuses_assetsReleased" => false,
                "projectsStatuses_class" => "success"
            ],
            5 => [
                "projectsStatuses_name" => "Dispatched",
                "projectsStatuses_description" => "Sent to client" ,
                "projectsStatuses_foregroundColour" => "#ffffff",
                "projectsStatuses_backgroundColour" => "#66ff66",
                "projectsStatuses_rank" => 5,
                "projectsStatuses_assetsReleased" => false,
                "projectsStatuses_class" => "primary"
            ],
            6 => [
                "projectsStatuses_name" => "Returned",
                "projectsStatuses_description" => "Waiting to be checked in ",
                "projectsStatuses_foregroundColour" => "#000000",
                "projectsStatuses_backgroundColour" => "#ffdd99",
                "projectsStatuses_rank" => 6,
                "projectsStatuses_assetsReleased" => false,
                "projectsStatuses_class" => "primary"
            ],
            7 => [
                "projectsStatuses_name" => "Closed",
                "projectsStatuses_description" => "Pending move to Archive",
                "projectsStatuses_foregroundColour" => "#000000",
                "projectsStatuses_backgroundColour" => "#F5F5F5",
                "projectsStatuses_rank" => 7,
                "projectsStatuses_assetsReleased" => false,
                "projectsStatuses_class" => "secondary"
            ],
            8 => [
                "projectsStatuses_name" => "Cancelled",
                "projectsStatuses_description" => "Project Cancelled",
                "projectsStatuses_foregroundColour" => "#000000",
                "projectsStatuses_backgroundColour" => "#F5F5F5",
                "projectsStatuses_rank" => 8,
                "projectsStatuses_assetsReleased" => true,
                "projectsStatuses_class" => "danger"
            ],
            9 => [
                "projectsStatuses_name" => "Lead Lost",
                "projectsStatuses_description" => "Project Cancelled",
                "projectsStatuses_foregroundColour" => "#000000",
                "projectsStatuses_backgroundColour" => "#F5F5F5",
                "projectsStatuses_rank" => 9,
                "projectsStatuses_assetsReleased" => true,
                "projectsStatuses_class" => "danger"
            ]
        ];


        $this->table('projectsStatuses', [
                'id' => false,
                'primary_key' => ['projectsStatuses_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('projectsStatuses_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('projectsStatuses_name', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'projectsStatuses_id',
            ])
            ->addColumn('projectsStatuses_description', 'string', [
                'null' => false,
                'limit' => 9000,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'projectsStatuses_name',
            ])
            ->addColumn('projectsStatuses_fontAwesome', 'string', [
                'null' => true,
                'limit' => 100,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'projectsStatuses_name',
            ])
            ->addColumn('projectsStatuses_foregroundColour', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'projectsStatuses_fontAwesome',
            ])
            ->addColumn('projectsStatuses_backgroundColour', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'projectsStatuses_foregroundColour',
            ])
            ->addColumn('projectsStatuses_class', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'projectsStatuses_backgroundColour',
            ])
            ->addColumn('projectsStatuses_rank', 'integer', [
                'null' => false,
                'default' => '999',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsStatuses_fontAwesome',
            ])
            ->addColumn('projectsStatuses_assetsReleased', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'projectsStatuses_rank',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsStatusesGroups_id',
            ])
            ->addColumn('projectsStatuses_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'instances_id',
            ])
            ->addIndex(['instances_id'], [
                'name' => 'projectsStatuses_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 'projectsStatuses_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();

        $this->table('projects')
            ->changeColumn('projects_status','integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR
            ])
            ->save();
        
        /**
         * In order to update the existing statuses, you need to make sure the auto increment starting value is higher than the highest existing status id, otherwise existing updates will over-write each other
         */
        $autoIncrementStartingValue = 1000;
        
        $instances = $this->fetchAll('SELECT instances_id FROM instances');   
        foreach ($instances as $instance) {
            foreach ($originalStatuses as $statusId => $status) {
                $status['instances_id'] = $instance['instances_id'];
                $status['projectsStatuses_id'] = $autoIncrementStartingValue++;
                $this->table('projectsStatuses')->insert($status)->saveData();
                $thisId = $this->getAdapter()->getConnection()->lastInsertId();
                if (!$thisId) throw new Exception('Failed to insert status');
                $this->execute('UPDATE projects SET projects_status = ' . intVal($thisId) . ' WHERE (instances_id=' . $instance['instances_id']. ' AND projects_status=' . $statusId . ')');
            }
        }

        $this->table('projects')
            ->renameColumn('projects_status', 'projectsStatuses_id')
            ->save();

        $this->table('projects')
        ->addForeignKey('projectsStatuses_id', 'projectsStatuses', 'projectsStatuses_id', [
            'constraint' => 'projects_projectsStatuses_id_projectsStatuses_id_fk',
            'update' => 'CASCADE',
            'delete' => 'CASCADE',
        ])
        ->save();
        
        $this->execute("UPDATE instanceActionsCategories SET instanceActionsCategories_name = 'Project Types & Statuses' WHERE instanceActionsCategories_id=14;");

        $this->table('instanceActions')->insert([
            'instanceActions_id' => 134,
            'instanceActions_name' => 'View list of Project Statuses',
            'instanceActionsCategories_id' => 14,
            'instanceActions_dependent' => null,
            'instanceActions_incompatible' => null
        ])->saveData();

        $this->table('instanceActions')->insert([
            'instanceActions_id' => 135,
            'instanceActions_name' => 'Add new Project Status',
            'instanceActionsCategories_id' => 14,
            'instanceActions_dependent' => '134,136',
            'instanceActions_incompatible' => null
        ])->saveData();

        $this->table('instanceActions')->insert([
            'instanceActions_id' => 136,
            'instanceActions_name' => 'Edit Project Statuses',
            'instanceActionsCategories_id' => 14,
            'instanceActions_dependent' => '134',
            'instanceActions_incompatible' => null
        ])->saveData();

        $this->table('instanceActions')->insert([
            'instanceActions_id' => 137,
            'instanceActions_name' => 'Delete Project Statuses',
            'instanceActionsCategories_id' => 14,
            'instanceActions_dependent' => '134,136',
            'instanceActions_incompatible' => null
        ])->saveData();
    }
}
