<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CustomProjectStatus extends AbstractMigration
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
        //add a custom project status column to the projects table
        $this->table('projects')
            ->addColumn('projects_customProjectStatus', 'string', [
                'null' => true,
                'after' => 'projects_status'
            ])
            ->save();
    }
}
