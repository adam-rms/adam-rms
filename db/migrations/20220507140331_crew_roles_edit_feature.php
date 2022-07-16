<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CrewRolesEditFeature extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("UPDATE instanceActions SET instanceActions_name = 'Edit/Delete Crew Assignments' WHERE instanceActions_id = 49;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("UPDATE instanceActions SET instanceActions_name = 'Delete Crew Assignment' WHERE instanceActions_id = 49;");
    }
}
