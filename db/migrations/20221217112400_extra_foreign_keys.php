<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ExtraForeignKeys extends AbstractMigration
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
        $this->table('locationsBarcodes')
        ->addForeignKey('locations_id', 'locations', 'locations_id', [
            'constraint' => 'locationsBarcodes_locations_locations_id_fk',
            'update' => 'CASCADE',
            'delete' => 'CASCADE',
        ])
        ->save();

        $this->table('maintenanceJobs')
        ->addForeignKey('instances_id', 'instances', 'instances_id', [
            'constraint' => 'maintenanceJobs_instances_instances_id_fk',
            'update' => 'CASCADE',
            'delete' => 'CASCADE',
        ])
        ->save();

        $this->table('auditLog')
        ->dropForeignKey('users_userid')
        ->addForeignKey('users_userid', 'users', 'users_userid', [
            'constraint' => 'auditLog_users_users_userid_fk',
            'update' => 'CASCADE',
            'delete' => 'SET_NULL',
        ])
        ->save();
    }
}
