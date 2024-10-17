<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class UpdateAssetCategories extends AbstractMigration
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
        $this->table('assetCategoriesGroups')
            ->addColumn('instances_id', 'integer')
            ->addColumn('assetCategoriesGroups_deleted', 'boolean', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'instances_id',
            ])
            ->addIndex(['instances_id'], [
                'name' => 'assetCategoriesGroups_instances_instances_id_fk',
                'unique' => false,
            ])
            ->addForeignKey('instances_id', 'instances', 'instances_id', [
                'constraint' => 'assetCategoriesGroups_instances_instances_id_fk',
                'update' => 'CASCADE',
                'delete' => 'CASCADE',
            ])
            ->save();
    }
}
