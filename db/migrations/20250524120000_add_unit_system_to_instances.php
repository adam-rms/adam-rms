<?php

use Phinx\Migration\AbstractMigration;

class AddUnitSystemToInstances extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('instances');
        $table->addColumn('instances_unitSystem', 'string', [
            'default' => 'metric',
            'limit' => 10,
            'null' => false, // It has a default, so should not be null
        ])
            ->save();
    }
}
