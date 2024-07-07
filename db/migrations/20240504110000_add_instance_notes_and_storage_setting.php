<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class AddInstanceNotesAndStorageSetting extends AbstractMigration
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
            ->table('instances')
            ->addColumn(
                'instances_storageEnabled',
                'boolean',
                [
                    'null' => false,
                    'limit' => MysqlAdapter::INT_TINY,
                    'default' => 1,
                    'after' => 'instances_storageLimit'
                ]
            )
            ->addColumn(
                'instances_serverNotes',
                'text',
                [
                    'null' => true,
                    'default' => null,
                    'after' => 'instances_plan'
                ]
            )
            ->addColumn('instances_userLimit', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => '0 is unlimited',
                'after' => 'instances_storageLimit',
            ])
            ->addColumn('instances_assetLimit', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => '0 is unlimited',
                'after' => 'instances_storageLimit',
            ])
            ->addColumn('instances_billingUser', 'integer', [
                'null' => true,
                'default' => null,
                'limit' => MysqlAdapter::INT_REGULAR
            ])
            ->addForeignKey('instances_billingUser', 'users', 'users_userid', [
                'constraint' => 'instancesBillingUser_users_users_userid_fk',
                'update' => 'CASCADE',
                'delete' => 'SET_NULL',
            ])
            ->addColumn('instances_stripe_subscription', 'string', [
                'null' => true,
                'limit' => 200,
                'default' => null,
            ])
            ->save();
    }
}
