<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class GrantScheduledMaintenancePermissions extends AbstractMigration
{
    /**
     * Grant scheduled maintenance permissions to existing positions that have MAINTENANCE_JOBS:VIEW
     */
    public function up(): void
    {
        echo "Granting scheduled maintenance permissions to existing positions...\n";

        $newPermissions = [
            'MAINTENANCE_JOBS:SCHEDULES:VIEW',
            'MAINTENANCE_JOBS:SCHEDULES:CREATE',
            'MAINTENANCE_JOBS:SCHEDULES:EDIT',
            'MAINTENANCE_JOBS:SCHEDULES:DELETE',
        ];

        $builder = $this->getQueryBuilder();

        // Get all positions that have MAINTENANCE_JOBS:VIEW (positions that can manage maintenance)
        $positionsQuery = $builder->select(['instancePositions_id', 'instancePositions_actions'])
            ->from('instancePositions')
            ->where(['instancePositions_actions LIKE' => '%MAINTENANCE_JOBS:VIEW%'])
            ->execute();

        $positions = $positionsQuery->fetchAll();

        $updatedCount = 0;
        foreach ($positions as $position) {
            $positionId = $position[0];
            $currentActions = $position[1];

            if (!$currentActions) {
                continue;
            }

            $actionsArray = explode(',', $currentActions);

            // Add new permissions if they don't already exist
            $modified = false;
            foreach ($newPermissions as $permission) {
                if (!in_array($permission, $actionsArray)) {
                    $actionsArray[] = $permission;
                    $modified = true;
                }
            }

            // Only update if we actually added new permissions
            if ($modified) {
                $updatedActions = implode(',', $actionsArray);

                // Update the position
                $this->execute(
                    "UPDATE instancePositions SET instancePositions_actions = :actions WHERE instancePositions_id = :id",
                    ['actions' => $updatedActions, 'id' => $positionId]
                );

                $updatedCount++;
                echo "  Granted scheduled maintenance permissions to position ID $positionId\n";
            }
        }

        echo "Scheduled maintenance permissions granted to $updatedCount position(s)\n";
    }

    public function down(): void
    {
        echo "Removing scheduled maintenance permissions from positions...\n";

        $permissionsToRemove = [
            'MAINTENANCE_JOBS:SCHEDULES:VIEW',
            'MAINTENANCE_JOBS:SCHEDULES:CREATE',
            'MAINTENANCE_JOBS:SCHEDULES:EDIT',
            'MAINTENANCE_JOBS:SCHEDULES:DELETE',
        ];

        $builder = $this->getQueryBuilder();

        // Get all positions
        $positionsQuery = $builder->select(['instancePositions_id', 'instancePositions_actions'])
            ->from('instancePositions')
            ->execute();

        $positions = $positionsQuery->fetchAll();

        $updatedCount = 0;
        foreach ($positions as $position) {
            $positionId = $position[0];
            $currentActions = $position[1];

            if (!$currentActions) {
                continue;
            }

            $actionsArray = explode(',', $currentActions);

            // Remove the scheduled maintenance permissions
            $originalCount = count($actionsArray);
            $actionsArray = array_diff($actionsArray, $permissionsToRemove);

            // Only update if we actually removed permissions
            if (count($actionsArray) < $originalCount) {
                $updatedActions = implode(',', $actionsArray);

                $this->execute(
                    "UPDATE instancePositions SET instancePositions_actions = :actions WHERE instancePositions_id = :id",
                    ['actions' => $updatedActions, 'id' => $positionId]
                );

                $updatedCount++;
                echo "  Removed scheduled maintenance permissions from position ID $positionId\n";
            }
        }

        echo "Scheduled maintenance permissions removed from $updatedCount position(s)\n";
    }
}