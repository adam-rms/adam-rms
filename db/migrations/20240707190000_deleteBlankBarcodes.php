<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class DeleteBlankBarcodes extends AbstractMigration
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
        $query = $this->getQueryBuilder();
        $query
            ->delete('assetsBarcodes')
            ->where(function ($exp) {
                return $exp
                    ->isNull('assets_id');
            })
            ->execute();
        $this->table('assetsBarcodes')
            ->changeColumn('assets_id', 'integer', [
                'null' => false,
            ])
            ->save();
    }
}
