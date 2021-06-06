<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPageViewLogAction extends AbstractMigration
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
        $singleRow = [
            'instanceActions_id'    => 128,
            'instanceActions_name'  => 'View a CMS page access log',
            'instanceActionsCategories_id' => 15,
            'instanceActions_dependent' => 125,
            'instanceActions_incompatible' => null
        ];
        $table = $this->table('instanceActions');
        $table->insert($singleRow);
        $table->saveData();
    }
}
