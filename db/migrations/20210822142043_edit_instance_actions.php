<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class EditInstanceActions extends AbstractMigration
{
    /**
     * Migrate Up.
     * This is editing an existing entry, and so can only work in one direction.
     */
    public function up()
    {
        $builder = $this->getQueryBuilder();
        $builder
            ->update('instanceActions')
            ->set('instanceActions_dependent', '109')
            ->where(['instanceActions_id' => 112])
            ->execute();
    }

    /**
     * Migrate Down
     * Dunno why you'd want to reverse this, but you can if you want
     */
    public function down()
    {
        $builder = $this->getQueryBuilder();
        $builder
            ->update('instanceActions')
            ->set('instanceActions_dependent', '109,112')
            ->where(['instanceActions_id' => 112])
            ->execute();
    }
}