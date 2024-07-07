<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AddBarcodeScanValidationField extends AbstractMigration
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
        $this->table('assetsBarcodesScans')
            ->addColumn('assetsBarcodesScans_validation', 'string', [
                'null' => true,
                'default' => null,
                'after' => 'assetsBarcodes_customLocation'
            ])
            ->addColumn('assetsBarcodesScans_barcodeWasScanned', 'boolean', [
                'null' => false,
                'limit' => MysqlAdapter::INT_TINY,
                'default' => 1,
                'after' => 'assetsBarcodes_customLocation'
            ])
            ->save();
    }
}
