<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class TweakInstancePlanLogic extends AbstractMigration
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
            ->renameColumn('instances_plan', 'instances_planName')
            ->renameColumn('instances_stripe_subscription', 'instances_planStripeCustomerId')
            ->addColumn(
                'instances_suspended',
                'boolean',
                [
                    'null' => false,
                    'limit' => MysqlAdapter::INT_TINY,
                    'default' => 0,
                    'after' => 'instances_deleted'
                ]
            )
            ->addColumn('instances_suspendedReason', 'string', [
                'null' => true,
                'limit' => 200,
                'default' => null,
            ])
            ->addColumn('instances_suspendedReasonType', 'string', [
                'null' => true,
                'limit' => 200,
                'default' => null,
                'comment' => 'noplan = Need to setup a subscription. billing = Issue with plan, need to go to billing portal. other = Other reason.',
            ])
            ->addColumn('instances_projectLimit', 'integer', [
                'null' => false,
                'default' => '0',
                'limit' => MysqlAdapter::INT_REGULAR,
                'comment' => '0 is unlimited',
                'after' => 'instances_storageLimit',
            ])
            ->save();
    }
}
