<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ExportPermission extends AbstractMigration
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
        $table = $this->table('instanceActions');
        $row = [
            'instanceActions_id'    => 134,
            'instanceActions_name'  => 'Export instance data',
            'instanceActionsCategories_id' => 9,
            'instanceActions_dependent' => null,
            'instanceActions_incompatible' => null
        ];
        $table->insert($row);
        $table->saveData();
    }
}
