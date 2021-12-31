<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateInstanceActions extends AbstractMigration
{
    /**
     * Migrate Up.
     * Fixes issues with instanceactions
     * @link https://github.com/adam-rms/adam-rms/issues/228
     */
    public function up()
    {
        $this->execute("UPDATE instanceActions t SET t.instanceActions_dependent = '36' WHERE t.instanceActions_id = 37;");
        $this->execute("UPDATE instanceActions t SET t.instanceActions_dependent = '2' WHERE t.instanceActions_id = 3;");
    }

    /**
     * Migrate Down. - one way as is a data bugfix
     */
    public function down()
    {
        //none
    }
}