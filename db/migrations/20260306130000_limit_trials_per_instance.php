<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class LimitTrialsPerInstance extends AbstractMigration
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
                'instances_hadTrial',
                'boolean',
                [
                    'null' => false,
                    'limit' => MysqlAdapter::INT_TINY,
                    'default' => 0,
                    'comment' => 'Whether this instance has ever had a free trial. Used to limit trials to one per instance.',
                    'after' => 'instances_planStripeCustomerId'
                ]
            )
            ->save();
    }
}
