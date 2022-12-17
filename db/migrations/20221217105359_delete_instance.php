<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DeleteInstance extends AbstractMigration
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
        $table = $this->table('actions');
        $row1 = [
            'actions_id'    => 23,
            'actions_name'  => 'Delete Instance',
            'actionsCategories_id' => 4,
            'actions_dependent' => 20,
            'actions_incompatible' => null
        ];
        $table->insert($row1);
        $table->saveData();
    }
}
