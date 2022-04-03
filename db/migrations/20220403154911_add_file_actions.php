<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddFileActions extends AbstractMigration
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
        $row1 = [
            'instanceActions_id'    => 129,
            'instanceActions_name'  => 'Access the File Browser',
            'instanceActionsCategories_id' => 7,
            'instanceActions_dependent' => null,
            'instanceActions_incompatible' => null
        ];
        $table->insert($row1);
        $row2 = [
            'instanceActions_id'    => 130,
            'instanceActions_name'  => 'Upload files to the File Browser',
            'instanceActionsCategories_id' => 7,
            'instanceActions_dependent' => 129,
            'instanceActions_incompatible' => null
        ];
        $table->insert($row2);
        $row3 = [
            'instanceActions_id'    => 131,
            'instanceActions_name'  => 'Move files in the File Browser',
            'instanceActionsCategories_id' => 7,
            'instanceActions_dependent' => 129,
            'instanceActions_incompatible' => null
        ];
        $table->insert($row3);
        $table->saveData();

        $this->execute("UPDATE instanceActions SET instanceActionsCategories_id = '3' WHERE instanceActions_id = 54 OR instanceActions_id = 55;"); //Move asset type files actions to assets
        $this->execute("UPDATE instanceActions SET instanceActionsCategories_id = '3' WHERE instanceActions_id = 61 OR instanceActions_id = 62;"); //Move asset files actions to assets
        $this->execute("UPDATE instanceActions SET instanceActionsCategories_id = '11' WHERE instanceActions_id = 100 OR instanceActions_id = 101;"); //Move location files actions to locations
        $this->execute("UPDATE instanceActions SET instanceActionsCategories_id = '4' WHERE instanceActions_id = 102;"); //Move project files actions to projects
    }
}
