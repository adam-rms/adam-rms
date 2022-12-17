<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ForeignKeyTweaks extends AbstractMigration
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
        $this->table('assets')
        ->dropForeignKey('assetTypes_id')
        ->addForeignKey('assetTypes_id', 'assetTypes', 'assetTypes_id', [
            'constraint' => 'assets_assetTypes_assetTypes_id_fk',
            'update' => 'CASCADE',
            'delete' => 'CASCADE',
        ])
        ->save();

        $this->table('authTokens')
        ->dropForeignKey('users_userid')
        ->addForeignKey('users_userid', 'users', 'users_userid', [
            'constraint' => 'authTokens_users_users_userid_fk',
            'update' => 'CASCADE',
            'delete' => 'CASCADE',
        ])
        ->dropForeignKey('authTokens_adminId')
        ->addForeignKey('authTokens_adminId', 'users', 'users_userid', [
            'constraint' => 'authTokens_users_users_userid_fk_2',
            'update' => 'CASCADE',
            'delete' => 'CASCADE',
        ])->save();
    }
}
