<?php

use Phinx\Db\Adapter\MysqlAdapter;

class Base extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->execute('SET unique_checks=0; SET foreign_key_checks=0;');
        $this->table('actions', [
                'id' => false,
                'primary_key' => ['actions_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('actions_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('actions_name', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'actions_id',
            ])
            ->addColumn('actionsCategories_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'actions_name',
            ])
            ->addColumn('actions_dependent', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'actionsCategories_id',
            ])
            ->addColumn('actions_incompatible', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'actions_dependent',
            ])
            ->addIndex(['actionsCategories_id'], [
                'name' => 'actions_actionsCategories_actionsCategories_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('actionsCategories_id', 'actionsCategories', 'actionsCategories_id', [
                'constraint' => 'actions_actionsCategories_actionsCategories_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('actionsCategories', [
                'id' => false,
                'primary_key' => ['actionsCategories_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('actionsCategories_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('actionsCategories_name', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'actionsCategories_id',
            ])
            ->addColumn('actionsCategories_order', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'actionsCategories_name',
            ])
            ->create();
        $this->table('assetCategories', [
                'id' => false,
                'primary_key' => ['assetCategories_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('assetCategories_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('assetCategories_name', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'assetCategories_id',
            ])
            ->addColumn('assetCategories_fontAwesome', 'string', [
                'null' => true,
                'limit' => 100,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'assetCategories_name',
            ])
            ->addColumn('assetCategories_rank', 'integer', [
                'null' => false,
                'default' => '999',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetCategories_fontAwesome',
            ])
            ->addColumn('assetCategoriesGroups_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetCategories_rank',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetCategoriesGroups_id',
            ])
            ->addColumn('assetCategories_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'instances_id',
            ])
            ->addIndex(['instances_id'], [
                'name' => 'assetCategories_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addIndex(['assetCategoriesGroups_id'], [
                'name' => 'assetCategories_Groups_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('assetCategoriesGroups_id', 'assetCategoriesGroups', 'assetCategoriesGroups_id', [
                'constraint' => 'assetCategories_Groups_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 'assetCategories_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('assetCategoriesGroups', [
                'id' => false,
                'primary_key' => ['assetCategoriesGroups_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('assetCategoriesGroups_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('assetCategoriesGroups_name', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'assetCategoriesGroups_id',
            ])
            ->addColumn('assetCategoriesGroups_fontAwesome', 'string', [
                'null' => true,
                'limit' => 300,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'assetCategoriesGroups_name',
            ])
            ->addColumn('assetCategoriesGroups_order', 'integer', [
                'null' => false,
                'default' => '999',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetCategoriesGroups_fontAwesome',
            ])
            ->create();
        $this->table('assetGroups', [
                'id' => false,
                'primary_key' => ['assetGroups_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('assetGroups_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('assetGroups_name', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'assetGroups_id',
            ])
            ->addColumn('assetGroups_description', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'assetGroups_name',
            ])
            ->addColumn('assetGroups_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'assetGroups_description',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetGroups_deleted',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'users_userid',
            ])
            ->addIndex(['instances_id'], [
                'name' => 'assetGroups_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addIndex(['users_userid'], [
                'name' => 'assetGroups_users_users_userid_fk',
                'unique' => false,
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 'assetGroups_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'assetGroups_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('assets', [
                'id' => false,
                'primary_key' => ['assets_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('assets_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('assets_tag', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'comment' => 'The ID/Tag that the asset carries marked onto it',
                'after' => 'assets_id',
            ])
            ->addColumn('assetTypes_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assets_tag',
            ])
            ->addColumn('assets_notes', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'assetTypes_id',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assets_notes',
            ])
            ->addColumn('asset_definableFields_1', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instances_id',
            ])
            ->addColumn('asset_definableFields_2', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'asset_definableFields_1',
            ])
            ->addColumn('asset_definableFields_3', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'asset_definableFields_2',
            ])
            ->addColumn('asset_definableFields_4', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'asset_definableFields_3',
            ])
            ->addColumn('asset_definableFields_5', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'asset_definableFields_4',
            ])
            ->addColumn('asset_definableFields_6', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'asset_definableFields_5',
            ])
            ->addColumn('asset_definableFields_7', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'asset_definableFields_6',
            ])
            ->addColumn('asset_definableFields_8', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'asset_definableFields_7',
            ])
            ->addColumn('asset_definableFields_9', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'asset_definableFields_8',
            ])
            ->addColumn('asset_definableFields_10', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'asset_definableFields_9',
            ])
            ->addColumn('assets_inserted', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'asset_definableFields_10',
            ])
            ->addColumn('assets_dayRate', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assets_inserted',
            ])
            ->addColumn('assets_linkedTo', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assets_dayRate',
            ])
            ->addColumn('assets_weekRate', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assets_linkedTo',
            ])
            ->addColumn('assets_value', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assets_weekRate',
            ])
            ->addColumn('assets_mass', 'decimal', [
                'null' => true,
                'precision' => '55',
                'scale' => '5',
                'after' => 'assets_value',
            ])
            ->addColumn('assets_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'assets_mass',
            ])
            ->addColumn('assets_endDate', 'timestamp', [
                'null' => true,
                'after' => 'assets_deleted',
            ])
            ->addColumn('assets_archived', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'assets_endDate',
            ])
            ->addColumn('assets_assetGroups', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'assets_archived',
            ])
            ->addColumn('assets_storageLocation', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assets_assetGroups',
            ])
            ->addColumn('assets_showPublic', 'boolean', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'assets_storageLocation',
            ])
            ->addIndex(['assetTypes_id'], [
                'name' => 'assets_assetTypes_assetTypes_id_fk',
                'unique' => false,
            ])
            ->addIndex(['assets_linkedTo'], [
                'name' => 'assets_assets_assets_id_fk',
                'unique' => false,
            ])
            ->addIndex(['instances_id'], [
                'name' => 'assets_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addIndex(['assets_storageLocation'], [
                'name' => 'assets_locations_locations_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('assets_linkedTo', 'assets', 'assets_id', [
                'constraint' => 'assets_assets_assets_id_fk',
                'update' => 'SET_NULL',
                'delete' => 'SET_NULL',
            ])
            ->addForeignKey('assetTypes_id', 'assetTypes', 'assetTypes_id', [
                'constraint' => 'assets_assetTypes_assetTypes_id_fk',
                'update' => 'NO_ACTION',
                'delete' => 'NO_ACTION',
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 'assets_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('assets_storageLocation', 'locations', 'locations_id', [
                'constraint' => 'assets_locations_locations_id_fk',
                'update' => 'CASCADE',
                'delete' => 'SET_NULL',
            ])
            ->create();
        $this->table('assetsAssignments', [
                'id' => false,
                'primary_key' => ['assetsAssignments_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('assetsAssignments_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('assets_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetsAssignments_id',
            ])
            ->addColumn('projects_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assets_id',
            ])
            ->addColumn('assetsAssignments_comment', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'projects_id',
            ])
            ->addColumn('assetsAssignments_customPrice', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetsAssignments_comment',
            ])
            ->addColumn('assetsAssignments_discount', 'float', [
                'null' => false,
                'default' => '0',
                'after' => 'assetsAssignments_customPrice',
            ])
            ->addColumn('assetsAssignments_timestamp', 'timestamp', [
                'null' => true,
                'after' => 'assetsAssignments_discount',
            ])
            ->addColumn('assetsAssignments_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'assetsAssignments_timestamp',
            ])
            ->addColumn('assetsAssignmentsStatus_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => '0 = None applicable
10 = Pending pick
20 = Picked
30 = Prepping
40 = Tested
50 = Packed
60 = Dispatched
70 = Awaiting Check-in
80 = Case opened
90 = Unpacked
100 = Tested
110 = Stored',
                'after' => 'assetsAssignments_deleted',
            ])
            ->addColumn('assetsAssignments_linkedTo', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetsAssignmentsStatus_id',
            ])
            ->addIndex(['assets_id'], [
                'name' => 'assetsAssignments_assets_assets_id_fk',
                'unique' => false,
            ])
            ->addIndex(['projects_id'], [
                'name' => 'assetsAssignments_projects_projects_id_fk',
                'unique' => false,
            ])
            ->addIndex(['assetsAssignments_linkedTo'], [
                'name' => 'assetsAssignments_assetsAssignments_assetsAssignments_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('assetsAssignments_linkedTo', 'assetsAssignments', 'assetsAssignments_id', [
                'constraint' => 'assetsAssignments_assetsAssignments_assetsAssignments_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('assets_id', 'assets', 'assets_id', [
                'constraint' => 'assetsAssignments_assets_assets_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('projects_id', 'projects', 'projects_id', [
                'constraint' => 'assetsAssignments_projects_projects_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('assetsAssignmentsStatus', [
                'id' => false,
                'primary_key' => ['assetsAssignmentsStatus_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('assetsAssignmentsStatus_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetsAssignmentsStatus_id',
            ])
            ->addColumn('assetsAssignmentsStatus_name', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'instances_id',
            ])
            ->addColumn('assetsAssignmentsStatus_order', 'integer', [
                'null' => true,
                'default' => '999',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetsAssignmentsStatus_name',
            ])
            ->addIndex(['instances_id'], [
                'name' => 'assetsAssignmentsStatus_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 'assetsAssignmentsStatus_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('assetsBarcodes', [
                'id' => false,
                'primary_key' => ['assetsBarcodes_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('assetsBarcodes_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('assets_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetsBarcodes_id',
            ])
            ->addColumn('assetsBarcodes_value', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'assets_id',
            ])
            ->addColumn('assetsBarcodes_type', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'assetsBarcodes_value',
            ])
            ->addColumn('assetsBarcodes_notes', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'assetsBarcodes_type',
            ])
            ->addColumn('assetsBarcodes_added', 'timestamp', [
                'null' => false,
                'after' => 'assetsBarcodes_notes',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'Userid that added it',
                'after' => 'assetsBarcodes_added',
            ])
            ->addColumn('assetsBarcodes_deleted', 'boolean', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'users_userid',
            ])
            ->addIndex(['assets_id'], [
                'name' => 'assetsBarcodes_assets_assets_id_fk',
                'unique' => false,
            ])
            ->addIndex(['users_userid'], [
                'name' => 'assetsBarcodes_users_users_userid_fk',
                'unique' => false,
            ])
            ->addForeignKey('assets_id', 'assets', 'assets_id', [
                'constraint' => 'assetsBarcodes_assets_assets_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'assetsBarcodes_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'SET_NULL',
            ])
            ->create();
        $this->table('assetsBarcodesScans', [
                'id' => false,
                'primary_key' => ['assetsBarcodesScans_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('assetsBarcodesScans_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('assetsBarcodes_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetsBarcodesScans_id',
            ])
            ->addColumn('assetsBarcodesScans_timestamp', 'timestamp', [
                'null' => false,
                'after' => 'assetsBarcodes_id',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetsBarcodesScans_timestamp',
            ])
            ->addColumn('locationsBarcodes_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'users_userid',
            ])
            ->addColumn('location_assets_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'locationsBarcodes_id',
            ])
            ->addColumn('assetsBarcodes_customLocation', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'location_assets_id',
            ])
            ->addIndex(['assetsBarcodes_id'], [
                'name' => 'assetsBarcodesScans_assetsBarcodes_assetsBarcodes_id_fk',
                'unique' => false,
            ])
            ->addIndex(['users_userid'], [
                'name' => 'assetsBarcodesScans_users_users_userid_fk',
                'unique' => false,
            ])
            ->addIndex(['locationsBarcodes_id'], [
                'name' => 'assetsBarcodesScans_locationsBarcodes_locationsBarcodes_id_fk',
                'unique' => false,
            ])
            ->addIndex(['location_assets_id'], [
                'name' => 'assetsBarcodesScans_assets_assets_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('assetsBarcodes_id', 'assetsBarcodes', 'assetsBarcodes_id', [
                'constraint' => 'assetsBarcodesScans_assetsBarcodes_assetsBarcodes_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('location_assets_id', 'assets', 'assets_id', [
                'constraint' => 'assetsBarcodesScans_assets_assets_id_fk',
                'update' => 'CASCADE',
                'delete' => 'SET_NULL',
            ])
            ->addForeignKey('locationsBarcodes_id', 'locationsBarcodes', 'locationsBarcodes_id', [
                'constraint' => 'assetsBarcodesScans_locationsBarcodes_locationsBarcodes_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'assetsBarcodesScans_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'SET_NULL',
            ])
            ->create();
        $this->table('assetTypes', [
                'id' => false,
                'primary_key' => ['assetTypes_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('assetTypes_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('assetTypes_name', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'assetTypes_id',
            ])
            ->addColumn('assetCategories_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetTypes_name',
            ])
            ->addColumn('manufacturers_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetCategories_id',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'manufacturers_id',
            ])
            ->addColumn('assetTypes_description', 'string', [
                'null' => true,
                'limit' => 1000,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instances_id',
            ])
            ->addColumn('assetTypes_productLink', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'assetTypes_description',
            ])
            ->addColumn('assetTypes_definableFields', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'assetTypes_productLink',
            ])
            ->addColumn('assetTypes_mass', 'decimal', [
                'null' => true,
                'precision' => '55',
                'scale' => '5',
                'after' => 'assetTypes_definableFields',
            ])
            ->addColumn('assetTypes_inserted', 'timestamp', [
                'null' => true,
                'after' => 'assetTypes_mass',
            ])
            ->addColumn('assetTypes_dayRate', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetTypes_inserted',
            ])
            ->addColumn('assetTypes_weekRate', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetTypes_dayRate',
            ])
            ->addColumn('assetTypes_value', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'assetTypes_weekRate',
            ])
            ->addIndex(['assetCategories_id'], [
                'name' => 'assetTypes_assetCategories_assetCategories_id_fk',
                'unique' => false,
            ])
            ->addIndex(['manufacturers_id'], [
                'name' => 'assetTypes_manufacturers_manufacturers_id_fk',
                'unique' => false,
            ])
            ->addIndex(['instances_id'], [
                'name' => 'assetTypes_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('assetCategories_id', 'assetCategories', 'assetCategories_id', [
                'constraint' => 'assetTypes_assetCategories_assetCategories_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 'assetTypes_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('manufacturers_id', 'manufacturers', 'manufacturers_id', [
                'constraint' => 'assetTypes_manufacturers_manufacturers_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('auditLog', [
                'id' => false,
                'primary_key' => ['auditLog_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('auditLog_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('auditLog_actionType', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'auditLog_id',
            ])
            ->addColumn('auditLog_actionTable', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'auditLog_actionType',
            ])
            ->addColumn('auditLog_actionData', 'text', [
                'null' => true,
                'limit' => MysqlAdapter::TEXT_LONG,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'auditLog_actionTable',
            ])
            ->addColumn('auditLog_timestamp', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'auditLog_actionData',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'auditLog_timestamp',
            ])
            ->addColumn('auditLog_actionUserid', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'users_userid',
            ])
            ->addColumn('projects_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'auditLog_actionUserid',
            ])
            ->addColumn('auditLog_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'projects_id',
            ])
            ->addColumn('auditLog_targetID', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'auditLog_deleted',
            ])
            ->addIndex(['users_userid'], [
                'name' => 'auditLog_users_users_userid_fk',
                'unique' => false,
            ])
            ->addIndex(['auditLog_actionUserid'], [
                'name' => 'auditLog_users_users_userid_fk_2',
                'unique' => false,
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'auditLog_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('auditLog_actionUserid', 'users', 'users_userid', [
                'constraint' => 'auditLog_users_users_userid_fk_2',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('authTokens', [
                'id' => false,
                'primary_key' => ['authTokens_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('authTokens_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('authTokens_token', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'authTokens_id',
            ])
            ->addColumn('authTokens_created', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'authTokens_token',
            ])
            ->addColumn('authTokens_ipAddress', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'authTokens_created',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'authTokens_ipAddress',
            ])
            ->addColumn('authTokens_valid', 'boolean', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => '1 for true. 0 for false',
                'after' => 'users_userid',
            ])
            ->addColumn('authTokens_adminId', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'authTokens_valid',
            ])
            ->addColumn('authTokens_deviceType', 'string', [
                'null' => false,
                'limit' => 1000,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'authTokens_adminId',
            ])
            ->addIndex(['authTokens_token'], [
                'name' => 'token',
                'unique' => true,
            ])
            ->addIndex(['users_userid'], [
                'name' => 'authTokens_users_users_userid_fk',
                'unique' => false,
            ])
            ->addIndex(['authTokens_adminId'], [
                'name' => 'authTokens_users_users_userid_fk_2',
                'unique' => false,
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'authTokens_users_users_userid_fk',
                'update' => 'NO_ACTION',
                'delete' => 'NO_ACTION',
            ])
            ->addForeignKey('authTokens_adminId', 'users', 'users_userid', [
                'constraint' => 'authTokens_users_users_userid_fk_2',
                'update' => 'NO_ACTION',
                'delete' => 'NO_ACTION',
            ])
            ->create();
        $this->table('clients', [
                'id' => false,
                'primary_key' => ['clients_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('clients_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('clients_name', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'clients_id',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'clients_name',
            ])
            ->addColumn('clients_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'instances_id',
            ])
            ->addColumn('clients_website', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'clients_deleted',
            ])
            ->addColumn('clients_email', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'clients_website',
            ])
            ->addColumn('clients_notes', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'clients_email',
            ])
            ->addColumn('clients_address', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'clients_notes',
            ])
            ->addColumn('clients_phone', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'clients_address',
            ])
            ->addIndex(['instances_id'], [
                'name' => 'clients_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 'clients_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('cmsPages', [
                'id' => false,
                'primary_key' => ['cmsPages_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('cmsPages_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'cmsPages_id',
            ])
            ->addColumn('cmsPages_showNav', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'instances_id',
            ])
            ->addColumn('cmsPages_showPublic', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'cmsPages_showNav',
            ])
            ->addColumn('cmsPages_showPublicNav', 'boolean', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'cmsPages_showPublic',
            ])
            ->addColumn('cmsPages_visibleToGroups', 'string', [
                'null' => true,
                'limit' => 1000,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'cmsPages_showPublicNav',
            ])
            ->addColumn('cmsPages_navOrder', 'integer', [
                'null' => false,
                'default' => '999',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'cmsPages_visibleToGroups',
            ])
            ->addColumn('cmsPages_fontAwesome', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'cmsPages_navOrder',
            ])
            ->addColumn('cmsPages_name', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'cmsPages_fontAwesome',
            ])
            ->addColumn('cmsPages_description', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'cmsPages_name',
            ])
            ->addColumn('cmsPages_archived', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'cmsPages_description',
            ])
            ->addColumn('cmsPages_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'cmsPages_archived',
            ])
            ->addColumn('cmsPages_added', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'cmsPages_deleted',
            ])
            ->addColumn('cmsPages_subOf', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'cmsPages_added',
            ])
            ->addIndex(['instances_id'], [
                'name' => 'cmsPages_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addIndex(['cmsPages_subOf'], [
                'name' => 'cmsPages_cmsPages_cmsPages_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('cmsPages_subOf', 'cmsPages', 'cmsPages_id', [
                'constraint' => 'cmsPages_cmsPages_cmsPages_id_fk',
                'update' => 'CASCADE',
                'delete' => 'SET_NULL',
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 'cmsPages_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('cmsPagesDrafts', [
                'id' => false,
                'primary_key' => ['cmsPagesDrafts_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('cmsPagesDrafts_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('cmsPages_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'cmsPagesDrafts_id',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'cmsPages_id',
            ])
            ->addColumn('cmsPagesDrafts_timestamp', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'users_userid',
            ])
            ->addColumn('cmsPagesDrafts_data', 'json', [
                'null' => true,
                'after' => 'cmsPagesDrafts_timestamp',
            ])
            ->addColumn('cmsPagesDrafts_changelog', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'cmsPagesDrafts_data',
            ])
            ->addColumn('cmsPagesDrafts_revisionID', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'cmsPagesDrafts_changelog',
            ])
            ->addIndex(['cmsPages_id'], [
                'name' => 'cmsPagesDrafts_cmsPages_cmsPages_id_fk',
                'unique' => false,
            ])
            ->addIndex(['users_userid'], [
                'name' => 'cmsPagesDrafts_users_users_userid_fk',
                'unique' => false,
            ])
            ->addIndex(['cmsPagesDrafts_timestamp'], [
                'name' => 'cmsPagesDrafts_cmsPagesDrafts_timestamp_index',
                'unique' => false,
            ])
            ->addForeignKey('cmsPages_id', 'cmsPages', 'cmsPages_id', [
                'constraint' => 'cmsPagesDrafts_cmsPages_cmsPages_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'cmsPagesDrafts_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'SET_NULL',
            ])
            ->create();
        $this->table('cmsPagesViews', [
                'id' => false,
                'primary_key' => ['cmsPagesViews_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('cmsPagesViews_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('cmsPages_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'cmsPagesViews_id',
            ])
            ->addColumn('cmsPagesViews_timestamp', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'cmsPages_id',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'cmsPagesViews_timestamp',
            ])
            ->addColumn('cmsPages_type', 'boolean', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'users_userid',
            ])
            ->addIndex(['cmsPages_id'], [
                'name' => 'cmsPagesViews_cmsPages_cmsPages_id_fk',
                'unique' => false,
            ])
            ->addIndex(['users_userid'], [
                'name' => 'cmsPagesViews_users_users_userid_fk',
                'unique' => false,
            ])
            ->addIndex(['cmsPagesViews_timestamp'], [
                'name' => 'cmsPagesViews_cmsPagesViews_timestamp_index',
                'unique' => false,
            ])
            ->addForeignKey('cmsPages_id', 'cmsPages', 'cmsPages_id', [
                'constraint' => 'cmsPagesViews_cmsPages_cmsPages_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'cmsPagesViews_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'SET_NULL',
            ])
            ->create();
        $this->table('crewAssignments', [
                'id' => false,
                'primary_key' => ['crewAssignments_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('crewAssignments_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'crewAssignments_id',
            ])
            ->addColumn('projects_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'users_userid',
            ])
            ->addColumn('crewAssignments_personName', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'projects_id',
            ])
            ->addColumn('crewAssignments_role', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'crewAssignments_personName',
            ])
            ->addColumn('crewAssignments_comment', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'crewAssignments_role',
            ])
            ->addColumn('crewAssignments_deleted', 'boolean', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'crewAssignments_comment',
            ])
            ->addColumn('crewAssignments_rank', 'integer', [
                'null' => true,
                'default' => '99',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'crewAssignments_deleted',
            ])
            ->addIndex(['projects_id'], [
                'name' => 'crewAssignments_projects_projects_id_fk',
                'unique' => false,
            ])
            ->addIndex(['users_userid'], [
                'name' => 'crewAssignments_users_users_userid_fk',
                'unique' => false,
            ])
            ->addForeignKey('projects_id', 'projects', 'projects_id', [
                'constraint' => 'crewAssignments_projects_projects_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'crewAssignments_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('emailSent', [
                'id' => false,
                'primary_key' => ['emailSent_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('emailSent_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'emailSent_id',
            ])
            ->addColumn('emailSent_html', 'text', [
                'null' => false,
                'limit' => MysqlAdapter::TEXT_LONG,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'users_userid',
            ])
            ->addColumn('emailSent_subject', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'emailSent_html',
            ])
            ->addColumn('emailSent_sent', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'emailSent_subject',
            ])
            ->addColumn('emailSent_fromEmail', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'emailSent_sent',
            ])
            ->addColumn('emailSent_fromName', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'emailSent_fromEmail',
            ])
            ->addColumn('emailSent_toName', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'emailSent_fromName',
            ])
            ->addColumn('emailSent_toEmail', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'emailSent_toName',
            ])
            ->addIndex(['users_userid'], [
                'name' => 'emailSent_users_users_userid_fk',
                'unique' => false,
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'emailSent_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('emailVerificationCodes', [
                'id' => false,
                'primary_key' => ['emailVerificationCodes_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('emailVerificationCodes_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('emailVerificationCodes_code', 'string', [
                'null' => false,
                'limit' => 1000,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'emailVerificationCodes_id',
            ])
            ->addColumn('emailVerificationCodes_used', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'emailVerificationCodes_code',
            ])
            ->addColumn('emailVerificationCodes_timestamp', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'emailVerificationCodes_used',
            ])
            ->addColumn('emailVerificationCodes_valid', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'emailVerificationCodes_timestamp',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'emailVerificationCodes_valid',
            ])
            ->addIndex(['users_userid'], [
                'name' => 'emailVerificationCodes_users_users_userid_fk',
                'unique' => false,
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'emailVerificationCodes_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('instanceActions', [
                'id' => false,
                'primary_key' => ['instanceActions_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('instanceActions_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('instanceActions_name', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instanceActions_id',
            ])
            ->addColumn('instanceActionsCategories_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'instanceActions_name',
            ])
            ->addColumn('instanceActions_dependent', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instanceActionsCategories_id',
            ])
            ->addColumn('instanceActions_incompatible', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instanceActions_dependent',
            ])
            ->addIndex(['instanceActionsCategories_id'], [
                'name' => 'categories_fk',
                'unique' => false,
            ])
            ->addForeignKey('instanceActionsCategories_id', 'instanceActionsCategories', 'instanceActionsCategories_id', [
                'constraint' => 'categories_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('instanceActionsCategories', [
                'id' => false,
                'primary_key' => ['instanceActionsCategories_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('instanceActionsCategories_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('instanceActionsCategories_name', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instanceActionsCategories_id',
            ])
            ->addColumn('instanceActionsCategories_order', 'integer', [
                'null' => false,
                'default' => '999',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'instanceActionsCategories_name',
            ])
            ->create();
        $this->table('instancePositions', [
                'id' => false,
                'primary_key' => ['instancePositions_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('instancePositions_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'instancePositions_id',
            ])
            ->addColumn('instancePositions_displayName', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instances_id',
            ])
            ->addColumn('instancePositions_rank', 'integer', [
                'null' => false,
                'default' => '999',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'instancePositions_displayName',
            ])
            ->addColumn('instancePositions_actions', 'string', [
                'null' => true,
                'limit' => 5000,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instancePositions_rank',
            ])
            ->addColumn('instancePositions_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'instancePositions_actions',
            ])
            ->addIndex(['instances_id'], [
                'name' => 'instancePositions_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 'instancePositions_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('instances', [
                'id' => false,
                'primary_key' => ['instances_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('instances_name', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instances_id',
            ])
            ->addColumn('instances_deleted', 'boolean', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'instances_name',
            ])
            ->addColumn('instances_plan', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instances_deleted',
            ])
            ->addColumn('instances_address', 'string', [
                'null' => true,
                'limit' => 1000,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instances_plan',
            ])
            ->addColumn('instances_phone', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instances_address',
            ])
            ->addColumn('instances_email', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instances_phone',
            ])
            ->addColumn('instances_website', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instances_email',
            ])
            ->addColumn('instances_weekStartDates', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instances_website',
            ])
            ->addColumn('instances_logo', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'instances_weekStartDates',
            ])
            ->addColumn('instances_emailHeader', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'A 1200x600 image to be the header on their emails',
                'after' => 'instances_logo',
            ])
            ->addColumn('instances_termsAndPayment', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instances_emailHeader',
            ])
            ->addColumn('instances_storageLimit', 'integer', [
                'null' => false,
                'default' => '524288000',
                'limit' => MysqlAdapter::INT_BIG,
                'comment' => 'In bytes - 500mb is default',
                'after' => 'instances_termsAndPayment',
            ])
            ->addColumn('instances_config_linkedDefaultDiscount', 'double', [
                'null' => true,
                'default' => '100',
                'after' => 'instances_storageLimit',
            ])
            ->addColumn('instances_config_currency', 'string', [
                'null' => false,
                'default' => 'GBP',
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instances_config_linkedDefaultDiscount',
            ])
            ->addColumn('instances_cableColours', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instances_config_currency',
            ])
            ->addColumn('instances_publicConfig', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instances_cableColours',
            ])
            ->create();
        $this->table('locations', [
                'id' => false,
                'primary_key' => ['locations_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('locations_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('locations_name', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'locations_id',
            ])
            ->addColumn('clients_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'locations_name',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'clients_id',
            ])
            ->addColumn('locations_address', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'instances_id',
            ])
            ->addColumn('locations_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'locations_address',
            ])
            ->addColumn('locations_subOf', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'locations_deleted',
            ])
            ->addColumn('locations_notes', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'locations_subOf',
            ])
            ->addIndex(['clients_id'], [
                'name' => 'locations_clients_clients_id_fk',
                'unique' => false,
            ])
            ->addIndex(['instances_id'], [
                'name' => 'locations_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addIndex(['locations_subOf'], [
                'name' => 'locations_locations_locations_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('clients_id', 'clients', 'clients_id', [
                'constraint' => 'locations_clients_clients_id_fk',
                'update' => 'CASCADE',
                'delete' => 'SET_NULL',
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 'locations_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('locations_subOf', 'locations', 'locations_id', [
                'constraint' => 'locations_locations_locations_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('locationsBarcodes', [
                'id' => false,
                'primary_key' => ['locationsBarcodes_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('locationsBarcodes_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('locations_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'locationsBarcodes_id',
            ])
            ->addColumn('locationsBarcodes_value', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'locations_id',
            ])
            ->addColumn('locationsBarcodes_type', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'locationsBarcodes_value',
            ])
            ->addColumn('locationsBarcodes_notes', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'locationsBarcodes_type',
            ])
            ->addColumn('locationsBarcodes_added', 'timestamp', [
                'null' => false,
                'after' => 'locationsBarcodes_notes',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'Userid that added it',
                'after' => 'locationsBarcodes_added',
            ])
            ->addColumn('locationsBarcodes_deleted', 'boolean', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'users_userid',
            ])
            ->addIndex(['users_userid'], [
                'name' => 'locationsBarcodes_users_users_userid_fk',
                'unique' => false,
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'locationsBarcodes_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'SET_NULL',
            ])
            ->create();
        $this->table('loginAttempts', [
                'id' => false,
                'primary_key' => ['loginAttempts_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('loginAttempts_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('loginAttempts_timestamp', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'loginAttempts_id',
            ])
            ->addColumn('loginAttempts_textEntered', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'loginAttempts_timestamp',
            ])
            ->addColumn('loginAttempts_ip', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'loginAttempts_textEntered',
            ])
            ->addColumn('loginAttempts_blocked', 'boolean', [
                'null' => false,
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'loginAttempts_ip',
            ])
            ->addColumn('loginAttempts_successful', 'boolean', [
                'null' => false,
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'loginAttempts_blocked',
            ])
            ->create();
        $this->table('maintenanceJobs', [
                'id' => false,
                'primary_key' => ['maintenanceJobs_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('maintenanceJobs_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('maintenanceJobs_assets', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'maintenanceJobs_id',
            ])
            ->addColumn('maintenanceJobs_timestamp_added', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'maintenanceJobs_assets',
            ])
            ->addColumn('maintenanceJobs_timestamp_due', 'timestamp', [
                'null' => true,
                'after' => 'maintenanceJobs_timestamp_added',
            ])
            ->addColumn('maintenanceJobs_user_tagged', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'maintenanceJobs_timestamp_due',
            ])
            ->addColumn('maintenanceJobs_user_creator', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'maintenanceJobs_user_tagged',
            ])
            ->addColumn('maintenanceJobs_user_assignedTo', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'maintenanceJobs_user_creator',
            ])
            ->addColumn('maintenanceJobs_title', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'maintenanceJobs_user_assignedTo',
            ])
            ->addColumn('maintenanceJobs_faultDescription', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'maintenanceJobs_title',
            ])
            ->addColumn('maintenanceJobs_priority', 'integer', [
                'null' => false,
                'default' => '5',
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => '1 to 10',
                'after' => 'maintenanceJobs_faultDescription',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'maintenanceJobs_priority',
            ])
            ->addColumn('maintenanceJobs_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'instances_id',
            ])
            ->addColumn('maintenanceJobsStatuses_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'maintenanceJobs_deleted',
            ])
            ->addColumn('maintenanceJobs_flagAssets', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'maintenanceJobsStatuses_id',
            ])
            ->addColumn('maintenanceJobs_blockAssets', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'maintenanceJobs_flagAssets',
            ])
            ->addIndex(['maintenanceJobs_user_creator'], [
                'name' => 'maintenanceJobs_users_users_userid_fk',
                'unique' => false,
            ])
            ->addForeignKey('maintenanceJobs_user_creator', 'users', 'users_userid', [
                'constraint' => 'maintenanceJobs_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('maintenanceJobsMessages', [
                'id' => false,
                'primary_key' => ['maintenanceJobsMessages_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('maintenanceJobsMessages_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('maintenanceJobs_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'maintenanceJobsMessages_id',
            ])
            ->addColumn('maintenanceJobsMessages_timestamp', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'maintenanceJobs_id',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'maintenanceJobsMessages_timestamp',
            ])
            ->addColumn('maintenanceJobsMessages_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'users_userid',
            ])
            ->addColumn('maintenanceJobsMessages_text', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'maintenanceJobsMessages_deleted',
            ])
            ->addColumn('maintenanceJobsMessages_file', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'maintenanceJobsMessages_text',
            ])
            ->addIndex(['maintenanceJobsMessages_file'], [
                'name' => 'maintenanceJobsMessages___files',
                'unique' => false,
            ])
            ->addIndex(['maintenanceJobs_id'], [
                'name' => 'maintenanceJobsMessages_maintenanceJobs_maintenanceJobs_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('maintenanceJobs_id', 'maintenanceJobs', 'maintenanceJobs_id', [
                'constraint' => 'maintenanceJobsMessages_maintenanceJobs_maintenanceJobs_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('maintenanceJobsMessages_file', 's3files', 's3files_id', [
                'constraint' => 'maintenanceJobsMessages___files',
                'update' => 'CASCADE',
                'delete' => 'SET_NULL',
            ])
            ->create();
        $this->table('maintenanceJobsStatuses', [
                'id' => false,
                'primary_key' => ['maintenanceJobsStatuses_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('maintenanceJobsStatuses_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'maintenanceJobsStatuses_id',
            ])
            ->addColumn('maintenanceJobsStatuses_name', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instances_id',
            ])
            ->addColumn('maintenanceJobsStatuses_order', 'boolean', [
                'null' => false,
                'default' => '99',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'maintenanceJobsStatuses_name',
            ])
            ->addColumn('maintenanceJobsStatuses_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'maintenanceJobsStatuses_order',
            ])
            ->addColumn('maintenanceJobsStatuses_showJobInMainList', 'boolean', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'maintenanceJobsStatuses_deleted',
            ])
            ->addIndex(['instances_id'], [
                'name' => 'maintenanceJobsStatuses_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 'maintenanceJobsStatuses_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('manufacturers', [
                'id' => false,
                'primary_key' => ['manufacturers_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('manufacturers_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('manufacturers_name', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'manufacturers_id',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'manufacturers_name',
            ])
            ->addColumn('manufacturers_internalAdamRMSNote', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instances_id',
            ])
            ->addColumn('manufacturers_website', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'manufacturers_internalAdamRMSNote',
            ])
            ->addColumn('manufacturers_notes', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'manufacturers_website',
            ])
            ->addIndex(['instances_id'], [
                'name' => 'manufacturers_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 'manufacturers_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('modules', [
                'id' => false,
                'primary_key' => ['modules_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('modules_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'modules_id',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => '"Author"',
                'after' => 'instances_id',
            ])
            ->addColumn('modules_name', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'users_userid',
            ])
            ->addColumn('modules_description', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'modules_name',
            ])
            ->addColumn('modules_learningObjectives', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'modules_description',
            ])
            ->addColumn('modules_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'modules_learningObjectives',
            ])
            ->addColumn('modules_show', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'modules_deleted',
            ])
            ->addColumn('modules_thumbnail', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'modules_show',
            ])
            ->addColumn('modules_type', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'modules_thumbnail',
            ])
            ->addIndex(['instances_id'], [
                'name' => 'modules_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addIndex(['users_userid'], [
                'name' => 'modules_users_users_userid_fk',
                'unique' => false,
            ])
            ->addIndex(['modules_thumbnail'], [
                'name' => 'modules_s3files_s3files_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 'modules_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('modules_thumbnail', 's3files', 's3files_id', [
                'constraint' => 'modules_s3files_s3files_id_fk',
                'update' => 'CASCADE',
                'delete' => 'SET_NULL',
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'modules_users_users_userid_fk',
                'update' => 'NO_ACTION',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('modulesSteps', [
                'id' => false,
                'primary_key' => ['modulesSteps_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('modulesSteps_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('modules_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'modulesSteps_id',
            ])
            ->addColumn('modulesSteps_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'modules_id',
            ])
            ->addColumn('modulesSteps_show', 'boolean', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'modulesSteps_deleted',
            ])
            ->addColumn('modulesSteps_name', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'modulesSteps_show',
            ])
            ->addColumn('modulesSteps_type', 'boolean', [
                'null' => false,
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'modulesSteps_name',
            ])
            ->addColumn('modulesSteps_content', 'text', [
                'null' => true,
                'limit' => MysqlAdapter::TEXT_LONG,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'modulesSteps_type',
            ])
            ->addColumn('modulesSteps_completionTime', 'integer', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'modulesSteps_content',
            ])
            ->addColumn('modulesSteps_internalNotes', 'text', [
                'null' => true,
                'limit' => MysqlAdapter::TEXT_LONG,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'modulesSteps_completionTime',
            ])
            ->addColumn('modulesSteps_order', 'integer', [
                'null' => false,
                'default' => '999',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'modulesSteps_internalNotes',
            ])
            ->addColumn('modulesSteps_locked', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => 'When set this is a like system level step that can\'t be edited',
                'after' => 'modulesSteps_order',
            ])
            ->addIndex(['modules_id'], [
                'name' => 'modulesSteps_modules_modules_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('modules_id', 'modules', 'modules_id', [
                'constraint' => 'modulesSteps_modules_modules_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('passwordResetCodes', [
                'id' => false,
                'primary_key' => ['passwordResetCodes_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('passwordResetCodes_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('passwordResetCodes_code', 'string', [
                'null' => false,
                'limit' => 1000,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'passwordResetCodes_id',
            ])
            ->addColumn('passwordResetCodes_used', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'passwordResetCodes_code',
            ])
            ->addColumn('passwordResetCodes_timestamp', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'passwordResetCodes_used',
            ])
            ->addColumn('passwordResetCodes_valid', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'passwordResetCodes_timestamp',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'passwordResetCodes_valid',
            ])
            ->addIndex(['users_userid'], [
                'name' => 'passwordResetCodes_users_users_userid_fk',
                'unique' => false,
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'passwordResetCodes_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('payments', [
                'id' => false,
                'primary_key' => ['payments_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('payments_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('payments_amount', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'payments_id',
            ])
            ->addColumn('payments_quantity', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'payments_amount',
            ])
            ->addColumn('payments_type', 'boolean', [
                'null' => false,
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => '1 = Payment Recieved
2 = Sales item
3 = SubHire item
4 = Staff cost',
                'after' => 'payments_quantity',
            ])
            ->addColumn('payments_reference', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'payments_type',
            ])
            ->addColumn('payments_date', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'payments_reference',
            ])
            ->addColumn('payments_supplier', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'payments_date',
            ])
            ->addColumn('payments_method', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'payments_supplier',
            ])
            ->addColumn('payments_comment', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'payments_method',
            ])
            ->addColumn('projects_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'payments_comment',
            ])
            ->addColumn('payments_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'projects_id',
            ])
            ->addIndex(['projects_id'], [
                'name' => 'payments_projects_projects_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('projects_id', 'projects', 'projects_id', [
                'constraint' => 'payments_projects_projects_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('positions', [
                'id' => false,
                'primary_key' => ['positions_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('positions_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('positions_displayName', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'positions_id',
            ])
            ->addColumn('positions_positionsGroups', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'positions_displayName',
            ])
            ->addColumn('positions_rank', 'integer', [
                'null' => false,
                'default' => '4',
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => 'Rank of the position - so that the most senior position for a user is shown as their "main one". 0 is the most senior',
                'after' => 'positions_positionsGroups',
            ])
            ->create();
        $this->table('positionsGroups', [
                'id' => false,
                'primary_key' => ['positionsGroups_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('positionsGroups_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('positionsGroups_name', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'positionsGroups_id',
            ])
            ->addColumn('positionsGroups_actions', 'string', [
                'null' => true,
                'limit' => 1000,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'positionsGroups_name',
            ])
            ->create();
        $this->table('projects', [
                'id' => false,
                'primary_key' => ['projects_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('projects_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('projects_name', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'projects_id',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projects_name',
            ])
            ->addColumn('projects_manager', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'instances_id',
            ])
            ->addColumn('projects_description', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'projects_manager',
            ])
            ->addColumn('projects_created', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'projects_description',
            ])
            ->addColumn('clients_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projects_created',
            ])
            ->addColumn('projects_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'clients_id',
            ])
            ->addColumn('projects_archived', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'projects_deleted',
            ])
            ->addColumn('projects_dates_use_start', 'timestamp', [
                'null' => true,
                'after' => 'projects_archived',
            ])
            ->addColumn('projects_dates_use_end', 'timestamp', [
                'null' => true,
                'after' => 'projects_dates_use_start',
            ])
            ->addColumn('projects_dates_deliver_start', 'timestamp', [
                'null' => true,
                'after' => 'projects_dates_use_end',
            ])
            ->addColumn('projects_dates_deliver_end', 'timestamp', [
                'null' => true,
                'after' => 'projects_dates_deliver_start',
            ])
            ->addColumn('projects_status', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => 'Provisional',
                'after' => 'projects_dates_deliver_end',
            ])
            ->addColumn('locations_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projects_status',
            ])
            ->addColumn('projects_invoiceNotes', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'locations_id',
            ])
            ->addColumn('projects_defaultDiscount', 'double', [
                'null' => false,
                'default' => '0',
                'after' => 'projects_invoiceNotes',
            ])
            ->addColumn('projectsTypes_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projects_defaultDiscount',
            ])
            ->addIndex(['clients_id'], [
                'name' => 'projects_clients_clients_id_fk',
                'unique' => false,
            ])
            ->addIndex(['instances_id'], [
                'name' => 'projects_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addIndex(['projects_manager'], [
                'name' => 'projects_users_users_userid_fk',
                'unique' => false,
            ])
            ->addIndex(['locations_id'], [
                'name' => 'projects_locations_locations_id_fk',
                'unique' => false,
            ])
            ->addIndex(['projectsTypes_id'], [
                'name' => 'projects_projectsTypes_projectsTypes_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('clients_id', 'clients', 'clients_id', [
                'constraint' => 'projects_clients_clients_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 'projects_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('locations_id', 'locations', 'locations_id', [
                'constraint' => 'projects_locations_locations_id_fk',
                'update' => 'CASCADE',
                'delete' => 'SET_NULL',
            ])
            ->addForeignKey('projects_manager', 'users', 'users_userid', [
                'constraint' => 'projects_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('projectsFinanceCache', [
                'id' => false,
                'primary_key' => ['projectsFinanceCache_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('projectsFinanceCache_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('projects_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsFinanceCache_id',
            ])
            ->addColumn('projectsFinanceCache_timestamp', 'timestamp', [
                'null' => false,
                'after' => 'projects_id',
            ])
            ->addColumn('projectsFinanceCache_timestampUpdated', 'timestamp', [
                'null' => true,
                'after' => 'projectsFinanceCache_timestamp',
            ])
            ->addColumn('projectsFinanceCache_equipmentSubTotal', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsFinanceCache_timestampUpdated',
            ])
            ->addColumn('projectsFinanceCache_equiptmentDiscounts', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsFinanceCache_equipmentSubTotal',
            ])
            ->addColumn('projectsFinanceCache_equiptmentTotal', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsFinanceCache_equiptmentDiscounts',
            ])
            ->addColumn('projectsFinanceCache_salesTotal', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsFinanceCache_equiptmentTotal',
            ])
            ->addColumn('projectsFinanceCache_staffTotal', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsFinanceCache_salesTotal',
            ])
            ->addColumn('projectsFinanceCache_externalHiresTotal', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsFinanceCache_staffTotal',
            ])
            ->addColumn('projectsFinanceCache_paymentsReceived', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsFinanceCache_externalHiresTotal',
            ])
            ->addColumn('projectsFinanceCache_grandTotal', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsFinanceCache_paymentsReceived',
            ])
            ->addColumn('projectsFinanceCache_value', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsFinanceCache_grandTotal',
            ])
            ->addColumn('projectsFinanceCache_mass', 'decimal', [
                'null' => true,
                'precision' => '55',
                'scale' => '5',
                'after' => 'projectsFinanceCache_value',
            ])
            ->addIndex(['projects_id'], [
                'name' => 'projectsFinanceCache_projects_projects_id_fk',
                'unique' => false,
            ])
            ->addIndex(['projectsFinanceCache_timestamp'], [
                'name' => 'projectFinnaceCacheTimestamp',
                'unique' => false,
            ])
            ->addForeignKey('projects_id', 'projects', 'projects_id', [
                'constraint' => 'projectsFinanceCache_projects_projects_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('projectsNotes', [
                'id' => false,
                'primary_key' => ['projectsNotes_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('projectsNotes_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('projectsNotes_title', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'projectsNotes_id',
            ])
            ->addColumn('projectsNotes_text', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'projectsNotes_title',
            ])
            ->addColumn('projectsNotes_userid', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsNotes_text',
            ])
            ->addColumn('projects_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsNotes_userid',
            ])
            ->addColumn('projectsNotes_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'projects_id',
            ])
            ->addIndex(['projects_id'], [
                'name' => 'projectsNotes_projects_projects_id_fk',
                'unique' => false,
            ])
            ->addIndex(['projectsNotes_userid'], [
                'name' => 'projectsNotes_users_users_userid_fk',
                'unique' => false,
            ])
            ->addForeignKey('projects_id', 'projects', 'projects_id', [
                'constraint' => 'projectsNotes_projects_projects_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('projectsNotes_userid', 'users', 'users_userid', [
                'constraint' => 'projectsNotes_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('projectsTypes', [
                'id' => false,
                'primary_key' => ['projectsTypes_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('projectsTypes_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('projectsTypes_name', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'projectsTypes_id',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsTypes_name',
            ])
            ->addColumn('projectsTypes_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'instances_id',
            ])
            ->addColumn('projectsTypes_config_finance', 'boolean', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'projectsTypes_deleted',
            ])
            ->addColumn('projectsTypes_config_files', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsTypes_config_finance',
            ])
            ->addColumn('projectsTypes_config_assets', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsTypes_config_files',
            ])
            ->addColumn('projectsTypes_config_client', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsTypes_config_assets',
            ])
            ->addColumn('projectsTypes_config_venue', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsTypes_config_client',
            ])
            ->addColumn('projectsTypes_config_notes', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsTypes_config_venue',
            ])
            ->addColumn('projectsTypes_config_crew', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsTypes_config_notes',
            ])
            ->addIndex(['instances_id'], [
                'name' => 'projectsTypes_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 'projectsTypes_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('projectsVacantRoles', [
                'id' => false,
                'primary_key' => ['projectsVacantRoles_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('projectsVacantRoles_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('projects_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsVacantRoles_id',
            ])
            ->addColumn('projectsVacantRoles_name', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'projects_id',
            ])
            ->addColumn('projectsVacantRoles_description', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'projectsVacantRoles_name',
            ])
            ->addColumn('projectsVacantRoles_personSpecification', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'projectsVacantRoles_description',
            ])
            ->addColumn('projectsVacantRoles_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'projectsVacantRoles_personSpecification',
            ])
            ->addColumn('projectsVacantRoles_open', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'projectsVacantRoles_deleted',
            ])
            ->addColumn('projectsVacantRoles_showPublic', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'projectsVacantRoles_open',
            ])
            ->addColumn('projectsVacantRoles_added', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'projectsVacantRoles_showPublic',
            ])
            ->addColumn('projectsVacantRoles_deadline', 'timestamp', [
                'null' => true,
                'after' => 'projectsVacantRoles_added',
            ])
            ->addColumn('projectsVacantRoles_firstComeFirstServed', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'projectsVacantRoles_deadline',
            ])
            ->addColumn('projectsVacantRoles_fileUploads', 'boolean', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'projectsVacantRoles_firstComeFirstServed',
            ])
            ->addColumn('projectsVacantRoles_slots', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsVacantRoles_fileUploads',
            ])
            ->addColumn('projectsVacantRoles_slotsFilled', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsVacantRoles_slots',
            ])
            ->addColumn('projectsVacantRoles_questions', 'json', [
                'null' => true,
                'after' => 'projectsVacantRoles_slotsFilled',
            ])
            ->addColumn('projectsVacantRoles_collectPhone', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'projectsVacantRoles_questions',
            ])
            ->addColumn('projectsVacantRoles_privateToPM', 'boolean', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'projectsVacantRoles_collectPhone',
            ])
            ->addIndex(['projects_id'], [
                'name' => 'projectsVacantRoles_projects_projects_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('projects_id', 'projects', 'projects_id', [
                'constraint' => 'projectsVacantRoles_projects_projects_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('projectsVacantRolesApplications', [
                'id' => false,
                'primary_key' => ['projectsVacantRolesApplications_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('projectsVacantRolesApplications_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('projectsVacantRoles_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsVacantRolesApplications_id',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'projectsVacantRoles_id',
            ])
            ->addColumn('projectsVacantRolesApplications_files', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'users_userid',
            ])
            ->addColumn('projectsVacantRolesApplications_phone', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'projectsVacantRolesApplications_files',
            ])
            ->addColumn('projectsVacantRolesApplications_applicantComment', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'projectsVacantRolesApplications_phone',
            ])
            ->addColumn('projectsVacantRolesApplications_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'projectsVacantRolesApplications_applicantComment',
            ])
            ->addColumn('projectsVacantRolesApplications_withdrawn', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'projectsVacantRolesApplications_deleted',
            ])
            ->addColumn('projectsVacantRolesApplications_submitted', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'projectsVacantRolesApplications_withdrawn',
            ])
            ->addColumn('projectsVacantRolesApplications_questionAnswers', 'json', [
                'null' => true,
                'after' => 'projectsVacantRolesApplications_submitted',
            ])
            ->addColumn('projectsVacantRolesApplications_status', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => '1 = Success
2 = Rejected',
                'after' => 'projectsVacantRolesApplications_questionAnswers',
            ])
            ->addIndex(['projectsVacantRoles_id'], [
                'name' => 'projectsVacantRolesApplications_projectsVacantRolesid_fk',
                'unique' => false,
            ])
            ->addIndex(['users_userid'], [
                'name' => 'projectsVacantRolesApplications_users_users_userid_fk',
                'unique' => false,
            ])
            ->addForeignKey('projectsVacantRoles_id', 'projectsVacantRoles', 'projectsVacantRoles_id', [
                'constraint' => 'projectsVacantRolesApplications_projectsVacantRolesid_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'projectsVacantRolesApplications_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('s3files', [
                'id' => false,
                'primary_key' => ['s3files_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('s3files_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 's3files_id',
            ])
            ->addColumn('s3files_path', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'comment' => 'NO LEADING /',
                'after' => 'instances_id',
            ])
            ->addColumn('s3files_name', 'string', [
                'null' => true,
                'limit' => 1000,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 's3files_path',
            ])
            ->addColumn('s3files_filename', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 's3files_name',
            ])
            ->addColumn('s3files_extension', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 's3files_filename',
            ])
            ->addColumn('s3files_original_name', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'comment' => 'What was this file originally called when it was uploaded? For things like file attachments
',
                'after' => 's3files_extension',
            ])
            ->addColumn('s3files_region', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 's3files_original_name',
            ])
            ->addColumn('s3files_endpoint', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 's3files_region',
            ])
            ->addColumn('s3files_cdn_endpoint', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 's3files_endpoint',
            ])
            ->addColumn('s3files_bucket', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 's3files_cdn_endpoint',
            ])
            ->addColumn('s3files_meta_size', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_BIG,
                'comment' => 'Size of the file in bytes',
                'after' => 's3files_bucket',
            ])
            ->addColumn('s3files_meta_public', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 's3files_meta_size',
            ])
            ->addColumn('s3files_meta_type', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => '0 = undefined
Rest are set out in corehead
',
                'after' => 's3files_meta_public',
            ])
            ->addColumn('s3files_meta_subType', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'Depends what it is - each module that uses the file handler will be setting this for themselves',
                'after' => 's3files_meta_type',
            ])
            ->addColumn('s3files_meta_uploaded', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 's3files_meta_subType',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'Who uploaded it?',
                'after' => 's3files_meta_uploaded',
            ])
            ->addColumn('s3files_meta_deleteOn', 'timestamp', [
                'null' => true,
                'comment' => 'Delete this file on this set date (basically if you hit delete we will kill it after say 30 days)',
                'after' => 'users_userid',
            ])
            ->addColumn('s3files_meta_physicallyStored', 'boolean', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'comment' => 'If we have the file it\'s 1 - if we deleted it it\'s 0 but the "deleteOn" is set. If we lost it it\'s 0 with a null "delete on"',
                'after' => 's3files_meta_deleteOn',
            ])
            ->addColumn('s3files_compressed', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 's3files_meta_physicallyStored',
            ])
            ->addIndex(['instances_id'], [
                'name' => 's3files_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addIndex(['users_userid'], [
                'name' => 's3files_users_users_userid_fk',
                'unique' => false,
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 's3files_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 's3files_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'SET_NULL',
            ])
            ->create();
        $this->table('signupCodes', [
                'id' => false,
                'primary_key' => ['signupCodes_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('signupCodes_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('signupCodes_name', 'string', [
                'null' => false,
                'limit' => 200,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'signupCodes_id',
            ])
            ->addColumn('instances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'signupCodes_name',
            ])
            ->addColumn('signupCodes_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'instances_id',
            ])
            ->addColumn('signupCodes_valid', 'boolean', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'signupCodes_deleted',
            ])
            ->addColumn('signupCodes_notes', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'signupCodes_valid',
            ])
            ->addColumn('signupCodes_role', 'string', [
                'null' => false,
                'limit' => 500,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'signupCodes_notes',
            ])
            ->addColumn('instancePositions_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'signupCodes_role',
            ])
            ->addIndex(['signupCodes_name'], [
                'name' => 'signupCodes_signupCodes_name_uindex',
                'unique' => true,
            ])
            ->addIndex(['instances_id'], [
                'name' => 'signupCodes_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addIndex(['instancePositions_id'], [
                'name' => 'signupCodes_instancePositions_instancePositions_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('instancePositions_id', 'instancePositions', 'instancePositions_id', [
                'constraint' => 'signupCodes_instancePositions_instancePositions_id_fk',
                'update' => 'CASCADE',
                'delete' => 'SET_NULL',
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 'signupCodes_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('userInstances', [
                'id' => false,
                'primary_key' => ['userInstances_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('userInstances_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'userInstances_id',
            ])
            ->addColumn('instancePositions_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'users_userid',
            ])
            ->addColumn('userInstances_extraPermissions', 'string', [
                'null' => true,
                'limit' => 5000,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'instancePositions_id',
            ])
            ->addColumn('userInstances_label', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'userInstances_extraPermissions',
            ])
            ->addColumn('userInstances_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'userInstances_label',
            ])
            ->addColumn('signupCodes_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'userInstances_deleted',
            ])
            ->addColumn('userInstances_archived', 'timestamp', [
                'null' => true,
                'after' => 'signupCodes_id',
            ])
            ->addIndex(['instancePositions_id'], [
                'name' => 'userInstances_instancePositions_instancePositions_id_fk',
                'unique' => false,
            ])
            ->addIndex(['users_userid'], [
                'name' => 'userInstances_users_users_userid_fk',
                'unique' => false,
            ])
            ->addIndex(['signupCodes_id'], [
                'name' => 'userInstances_signupCodes_signupCodes_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('instancePositions_id', 'instancePositions', 'instancePositions_id', [
                'constraint' => 'userInstances_instancePositions_instancePositions_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('signupCodes_id', 'signupCodes', 'signupCodes_id', [
                'constraint' => 'userInstances_signupCodes_signupCodes_id_fk',
                'update' => 'CASCADE',
                'delete' => 'SET_NULL',
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'userInstances_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('userModules', [
                'id' => false,
                'primary_key' => ['userModules_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('userModules_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('modules_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'userModules_id',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'modules_id',
            ])
            ->addColumn('userModules_stepsCompleted', 'string', [
                'null' => true,
                'limit' => 1000,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'users_userid',
            ])
            ->addColumn('userModules_currentStep', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'userModules_stepsCompleted',
            ])
            ->addColumn('userModules_started', 'timestamp', [
                'null' => false,
                'after' => 'userModules_currentStep',
            ])
            ->addColumn('userModules_updated', 'timestamp', [
                'null' => false,
                'after' => 'userModules_started',
            ])
            ->addIndex(['modules_id'], [
                'name' => 'userModules_modules_modules_id_fk',
                'unique' => false,
            ])
            ->addIndex(['users_userid'], [
                'name' => 'userModules_users_users_userid_fk',
                'unique' => false,
            ])
            ->addIndex(['userModules_currentStep'], [
                'name' => 'userModules_modulesSteps_modulesSteps_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('userModules_currentStep', 'modulesSteps', 'modulesSteps_id', [
                'constraint' => 'userModules_modulesSteps_modulesSteps_id_fk',
                'update' => 'CASCADE',
                'delete' => 'SET_NULL',
            ])
            ->addForeignKey('modules_id', 'modules', 'modules_id', [
                'constraint' => 'userModules_modules_modules_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'userModules_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('userModulesCertifications', [
                'id' => false,
                'primary_key' => ['userModulesCertifications_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_0900_ai_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('userModulesCertifications_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('modules_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'userModulesCertifications_id',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'modules_id',
            ])
            ->addColumn('userModulesCertifications_revoked', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'users_userid',
            ])
            ->addColumn('userModulesCertifications_approvedBy', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'userModulesCertifications_revoked',
            ])
            ->addColumn('userModulesCertifications_approvedComment', 'string', [
                'null' => true,
                'limit' => 2000,
                'collation' => 'utf8mb4_0900_ai_ci',
                'encoding' => 'utf8mb4',
                'after' => 'userModulesCertifications_approvedBy',
            ])
            ->addColumn('userModulesCertifications_timestamp', 'timestamp', [
                'null' => false,
                'after' => 'userModulesCertifications_approvedComment',
            ])
            ->addIndex(['users_userid'], [
                'name' => 'userModulesCertifications_users_users_userid_fk',
                'unique' => false,
            ])
            ->addIndex(['userModulesCertifications_approvedBy'], [
                'name' => 'userModulesCertifications_users_users_userid_fk_2',
                'unique' => false,
            ])
            ->addIndex(['modules_id'], [
                'name' => 'userModulesCertifications_modules_modules_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('modules_id', 'modules', 'modules_id', [
                'constraint' => 'userModulesCertifications_modules_modules_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'userModulesCertifications_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('userModulesCertifications_approvedBy', 'users', 'users_userid', [
                'constraint' => 'userModulesCertifications_users_users_userid_fk_2',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('userPositions', [
                'id' => false,
                'primary_key' => ['userPositions_id'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('userPositions_id', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'userPositions_id',
            ])
            ->addColumn('userPositions_start', 'timestamp', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'update' => 'CURRENT_TIMESTAMP',
                'after' => 'users_userid',
            ])
            ->addColumn('userPositions_end', 'timestamp', [
                'null' => true,
                'after' => 'userPositions_start',
            ])
            ->addColumn('positions_id', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'Can be null if you like - as long as you set the relevant other fields',
                'after' => 'userPositions_end',
            ])
            ->addColumn('userPositions_displayName', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'positions_id',
            ])
            ->addColumn('userPositions_extraPermissions', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'comment' => 'Allow a few extra permissions to be added just for this user for that exact permissions term
',
                'after' => 'userPositions_displayName',
            ])
            ->addColumn('userPositions_show', 'boolean', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'userPositions_extraPermissions',
            ])
            ->addIndex(['positions_id'], [
                'name' => 'userPositions_positions_positions_id_fk',
                'unique' => false,
            ])
            ->addIndex(['users_userid'], [
                'name' => 'userPositions_users_users_userid_fk',
                'unique' => false,
            ])
            ->addForeignKey('positions_id', 'positions', 'positions_id', [
                'constraint' => 'userPositions_positions_positions_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->addForeignKey('users_userid', 'users', 'users_userid', [
                'constraint' => 'userPositions_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->create();
        $this->table('users', [
                'id' => false,
                'primary_key' => ['users_userid'],
                'engine' => 'InnoDB',
                'encoding' => 'latin1',
                'collation' => 'latin1_swedish_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('users_username', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
            ])
            ->addColumn('users_name1', 'string', [
                'null' => true,
                'limit' => 100,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'users_username',
            ])
            ->addColumn('users_name2', 'string', [
                'null' => true,
                'limit' => 100,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'users_name1',
            ])
            ->addColumn('users_userid', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'identity' => 'enable',
                'after' => 'users_name2',
            ])
            ->addColumn('users_salty1', 'string', [
                'null' => true,
                'limit' => 30,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'users_userid',
            ])
            ->addColumn('users_password', 'string', [
                'null' => true,
                'limit' => 150,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'users_salty1',
            ])
            ->addColumn('users_salty2', 'string', [
                'null' => true,
                'limit' => 50,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'users_password',
            ])
            ->addColumn('users_hash', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'users_salty2',
            ])
            ->addColumn('users_email', 'string', [
                'null' => true,
                'limit' => 257,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'users_hash',
            ])
            ->addColumn('users_created', 'timestamp', [
                'null' => true,
                'default' => 'CURRENT_TIMESTAMP',
                'comment' => 'When user signed up',
                'after' => 'users_email',
            ])
            ->addColumn('users_notes', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'comment' => 'Internal Notes - Not visible to user',
                'after' => 'users_created',
            ])
            ->addColumn('users_thumbnail', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'users_notes',
            ])
            ->addColumn('users_changepass', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'users_thumbnail',
            ])
            ->addColumn('users_selectedProjectID', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'users_changepass',
            ])
            ->addColumn('users_selectedInstanceIDLast', 'integer', [
                'null' => true,
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => 'What is the instance ID they most recently selected? This will be the one we use next time they login',
                'after' => 'users_selectedProjectID',
            ])
            ->addColumn('users_suspended', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'users_selectedInstanceIDLast',
            ])
            ->addColumn('users_deleted', 'boolean', [
                'null' => true,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'users_suspended',
            ])
            ->addColumn('users_emailVerified', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'users_deleted',
            ])
            ->addColumn('users_social_facebook', 'string', [
                'null' => true,
                'limit' => 100,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'users_emailVerified',
            ])
            ->addColumn('users_social_twitter', 'string', [
                'null' => true,
                'limit' => 100,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'users_social_facebook',
            ])
            ->addColumn('users_social_instagram', 'string', [
                'null' => true,
                'limit' => 100,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'users_social_twitter',
            ])
            ->addColumn('users_social_linkedin', 'string', [
                'null' => true,
                'limit' => 100,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'users_social_instagram',
            ])
            ->addColumn('users_social_snapchat', 'string', [
                'null' => true,
                'limit' => 100,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'users_social_linkedin',
            ])
            ->addColumn('users_calendarHash', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'users_social_snapchat',
            ])
            ->addColumn('users_widgets', 'string', [
                'null' => true,
                'limit' => 500,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'users_calendarHash',
            ])
            ->addColumn('users_notificationSettings', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'users_widgets',
            ])
            ->addColumn('users_assetGroupsWatching', 'string', [
                'null' => true,
                'limit' => 200,
                'collation' => 'latin1_swedish_ci',
                'encoding' => 'latin1',
                'after' => 'users_notificationSettings',
            ])
            ->addIndex(['users_email'], [
                'name' => 'users_users_email_uindex',
                'unique' => true,
            ])
            ->addIndex(['users_username'], [
                'name' => 'users_users_username_uindex',
                'unique' => true,
            ])
            ->addIndex(['users_userid'], [
                'name' => 'username_2',
                'unique' => false,
            ])
            ->create();
        $this->execute('SET unique_checks=1; SET foreign_key_checks=1;');
        
        /** 
         * Setup the actions table
         */
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

        $table = $this->table('actionsCategories');
        $table->insert($categoryData)
            ->saveData();
        $table = $this->table('actions');
        $table->insert($actionData)
            ->saveData();

        /**
         * Setup the instance actions table
         */
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
                "instanceActions_dependent"=> "109",
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
