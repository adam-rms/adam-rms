<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TrustedDomainsPermission extends AbstractMigration
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
            'instanceActions_id'    => 133,
            'instanceActions_name'  => 'Manage Trusted Domains',
            'instanceActionsCategories_id' => 1,
            'instanceActions_dependent' => null,
            'instanceActions_incompatible' => null
        ];
        $table->insert($row1);
        $table->saveData();
    }
}
