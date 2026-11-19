<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddAssetTagPattern extends AbstractMigration
{
    public function change(): void
    {
        $this->table('instances')
            ->addColumn('instances_assetTagPattern', 'string', [
                'limit' => 200,
                'null' => true,
                'default' => null,
                'after' => 'instances_cableColours',
            ])
            ->update();
    }
}