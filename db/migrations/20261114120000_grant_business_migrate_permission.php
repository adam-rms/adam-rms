<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class GrantBusinessMigratePermission extends AbstractMigration
{
    /**
     * Gives the new BUSINESS:MIGRATE permission to every role that can already edit roles and permissions,
     * as those roles could give it to themselves anyway.
     */
    public function up(): void
    {
        $this->execute("UPDATE instancePositions SET instancePositions_actions = CONCAT(instancePositions_actions, ',BUSINESS:MIGRATE') WHERE FIND_IN_SET('BUSINESS:ROLES_AND_PERMISSIONS:EDIT', REPLACE(instancePositions_actions, ' ', '')) AND NOT FIND_IN_SET('BUSINESS:MIGRATE', REPLACE(instancePositions_actions, ' ', ''))");
    }

    public function down(): void
    {
        $this->execute("UPDATE instancePositions SET instancePositions_actions = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', REPLACE(instancePositions_actions, ' ', ''), ','), ',BUSINESS:MIGRATE,', ',')) WHERE FIND_IN_SET('BUSINESS:MIGRATE', REPLACE(instancePositions_actions, ' ', ''))");
    }
}
