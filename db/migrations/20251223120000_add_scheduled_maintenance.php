<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AddScheduledMaintenance extends AbstractMigration
{
    /**
     * Add scheduled maintenance system for assets
     * Allows automatic maintenance job creation based on due dates
     * Supports AS/NZS 3760 test and tag compliance workflows
     */
    public function change(): void
    {
        // Create assetMaintenanceSchedules table
        $this->table('assetMaintenanceSchedules', [
                'id' => false,
                'primary_key' => ['assetMaintenanceSchedules_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'comment' => 'Scheduled maintenance definitions for individual assets',
            ])
            ->addColumn('assetMaintenanceSchedules_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('assets_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'Asset this schedule applies to',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
            ])
            ->addColumn('assetMaintenanceSchedules_type', 'string', [
                'null' => false,
                'limit' => 100,
                'comment' => 'e.g., "Test and Tag", "PAT Testing", "Service"',
            ])
            ->addColumn('assetMaintenanceSchedules_nextDue', 'datetime', [
                'null' => false,
                'comment' => 'Next due date for this maintenance',
            ])
            ->addColumn('assetMaintenanceSchedules_intervalMonths', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_SMALL,
                'comment' => 'Suggested interval in months (nullable - user can override)',
            ])
            ->addColumn('assetMaintenanceSchedules_autoCreateJob', 'boolean', [
                'null' => false,
                'default' => 1,
                'comment' => 'Automatically create maintenance job when due',
            ])
            ->addColumn('assetMaintenanceSchedules_blockWhenOverdue', 'boolean', [
                'null' => false,
                'default' => 1,
                'comment' => 'Block asset assignment when overdue',
            ])
            ->addColumn('assetMaintenanceSchedules_lastJobCreated', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'Reference to last auto-created maintenanceJobs_id',
            ])
            ->addColumn('assetMaintenanceSchedules_jobCreatedDate', 'datetime', [
                'null' => true,
                'comment' => 'When the last job was auto-created',
            ])
            ->addColumn('assetMaintenanceSchedules_enabled', 'boolean', [
                'null' => false,
                'default' => 1,
            ])
            ->addColumn('assetMaintenanceSchedules_deleted', 'boolean', [
                'null' => false,
                'default' => 0,
            ])
            ->addColumn('assetMaintenanceSchedules_created', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addColumn('assetMaintenanceSchedules_updated', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
            ])
            ->addIndex(['assets_id', 'assetMaintenanceSchedules_deleted'], [
                'name' => 'idx_asset',
            ])
            ->addIndex(['instances_id', 'assetMaintenanceSchedules_nextDue'], [
                'name' => 'idx_instance_due',
            ])
            ->addIndex(['assetMaintenanceSchedules_nextDue', 'assetMaintenanceSchedules_enabled'], [
                'name' => 'idx_due_enabled',
            ])
            ->addForeignKey('assets_id', 'assets', 'assets_id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();

        // Add foreign key for lastJobCreated after maintenanceJobs column is added
        // (We'll add this after extending maintenanceJobs table)

        // Extend maintenanceJobs table
        $this->table('maintenanceJobs')
            ->addColumn('assetMaintenanceSchedules_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'Link to schedule if auto-created',
                'after' => 'maintenanceJobs_blockAssets',
            ])
            ->addIndex(['assetMaintenanceSchedules_id'], [
                'name' => 'idx_maintenance_schedule',
            ])
            ->addForeignKey('assetMaintenanceSchedules_id', 'assetMaintenanceSchedules', 'assetMaintenanceSchedules_id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->update();

        // Now add the foreign key from assetMaintenanceSchedules back to maintenanceJobs
        $this->table('assetMaintenanceSchedules')
            ->addForeignKey('assetMaintenanceSchedules_lastJobCreated', 'maintenanceJobs', 'maintenanceJobs_id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->update();

        echo "Scheduled maintenance system created successfully.\n";
    }
}