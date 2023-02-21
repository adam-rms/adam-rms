<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InstanceCalendarExport extends AbstractMigration
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
        // Write a phinx migration to add a calendarHash to the instances table
        $this->table('instances')
            ->addColumn('instances_calendarHash', 'string', [
                'null' => true,
                'limit' => 200,
                'after' => 'instances_emailHeader'
            ])
            ->save();
    }
}
