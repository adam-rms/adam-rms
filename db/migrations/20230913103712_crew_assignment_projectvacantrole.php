<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CrewAssignmentProjectVacantRole extends AbstractMigration
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
        $this->table('crewAssignments')
            ->addColumn('projectsVacantRoles_id', 'integer', [
                "null" => true,
                "default" => null,
                "after" => "crewAssignments_rank",
            ])
            ->addForeignKey('projectsVacantRoles_id', 'projectsVacantRoles', 'projectsVacantRoles_id', [
                'constraint' => 'crewAssignments_projectsVacantRoles_id_fk',
                'update' => 'CASCADE',
                'delete' => 'SET NULL',
            ])
            ->save();
    }
}
