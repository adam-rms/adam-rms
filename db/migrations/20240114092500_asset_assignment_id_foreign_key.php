<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AssetAssignmentIDForeignKey extends AbstractMigration
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
        $this->execute("
            UPDATE assetsAssignments SET assetsAssignmentsStatus_id = NULL
            WHERE NOT assetsAssignmentsStatus_id IN (
                SELECT assetsAssignmentsStatus_id
                FROM assetsAssignmentsStatus
            ) AND assetsAssignmentsStatus_id IS NOT NULL;
        ");
        $this->table('assetsAssignments')
        ->addForeignKey('assetsAssignmentsStatus_id', 'assetsAssignmentsStatus', 'assetsAssignmentsStatus_id', [
            'constraint' => 'assetsAssignments_assetsAssignmentsStatus_id_fk',
            'update' => 'CASCADE',
            'delete' => 'SET NULL',
        ])
        ->save();
    }
}
