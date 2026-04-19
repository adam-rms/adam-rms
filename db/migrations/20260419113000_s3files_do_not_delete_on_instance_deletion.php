<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class S3FilesDoNotDeleteOnInstanceDeletion extends AbstractMigration
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
        $this
            ->table('s3files')
            ->changeColumn(
                'instances_id',
                'integer',
                [
                    'null' => true,
                    'comment' => 'The ID of the instance this file is associated with. Nullable because files may be retained even after instance deletion.',
                ]
            )
            ->dropForeignKey('instances_id')
            ->addForeignKey(
                'instances_id',
                'instances',
                'instances_id',
                [
                    'constraint' => 's3files_instances_instances_id_fk',
                    'delete' => 'SET_NULL',
                    'update' => 'CASCADE',
                ]
            )
            ->update();
    }
}
