<?php

class ScheduledMaintenanceCreator
{
    private $DBLIB;
    private $bCMS;

    public function __construct()
    {
        global $DBLIB, $bCMS;
        $this->DBLIB = $DBLIB;
        $this->bCMS = $bCMS;
    }

    /**
     * Check and create maintenance jobs for all overdue schedules
     * Called on-demand (not via cron)
     * @param int|null $instanceId Optional instance ID to filter by
     * @return array Array with 'created' and 'errors' keys
     */
    public function processOverdueSchedules($instanceId = null)
    {
        $this->DBLIB->where("assetMaintenanceSchedules.assetMaintenanceSchedules_deleted", 0);
        $this->DBLIB->where("assetMaintenanceSchedules.assetMaintenanceSchedules_enabled", 1);
        $this->DBLIB->where("assetMaintenanceSchedules.assetMaintenanceSchedules_autoCreateJob", 1);
        $this->DBLIB->where("assetMaintenanceSchedules.assetMaintenanceSchedules_nextDue <= NOW()");

        // Only create jobs if we haven't already created one for this schedule
        $this->DBLIB->where("(assetMaintenanceSchedules.assetMaintenanceSchedules_lastJobCreated IS NULL OR assetMaintenanceSchedules.assetMaintenanceSchedules_jobCreatedDate < assetMaintenanceSchedules.assetMaintenanceSchedules_nextDue)");

        if ($instanceId !== null) {
            $this->DBLIB->where("assetMaintenanceSchedules.instances_id", $instanceId);
        }

        $this->DBLIB->join("assets", "assets.assets_id=assetMaintenanceSchedules.assets_id", "LEFT");
        $this->DBLIB->where("assets.assets_deleted", 0);

        $overdueSchedules = $this->DBLIB->get('assetMaintenanceSchedules', null, [
            'assetMaintenanceSchedules.*',
            'assets.assets_tag'
        ]);

        $created = [];
        $errors = [];

        foreach ($overdueSchedules as $schedule) {
            try {
                $jobId = $this->createMaintenanceJob($schedule);
                if ($jobId) {
                    $created[] = [
                        'schedule_id' => $schedule['assetMaintenanceSchedules_id'],
                        'job_id' => $jobId,
                        'asset_tag' => $schedule['assets_tag'],
                    ];
                }
            } catch (Exception $e) {
                $errors[] = [
                    'schedule_id' => $schedule['assetMaintenanceSchedules_id'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        return ['created' => $created, 'errors' => $errors];
    }

    /**
     * Create a maintenance job for a specific schedule ID (on-demand)
     * Used by assetFlagsAndBlocks() to create jobs when viewing assets
     * @param int $scheduleId The schedule ID
     * @return int|false The created job ID or false if not needed/failed
     */
    public function createJobForSchedule($scheduleId)
    {
        // Get the schedule with asset info
        $this->DBLIB->where("assetMaintenanceSchedules.assetMaintenanceSchedules_id", $scheduleId);
        $this->DBLIB->where("assetMaintenanceSchedules.assetMaintenanceSchedules_deleted", 0);
        $this->DBLIB->where("assetMaintenanceSchedules.assetMaintenanceSchedules_enabled", 1);
        $this->DBLIB->where("assetMaintenanceSchedules.assetMaintenanceSchedules_autoCreateJob", 1);
        $this->DBLIB->join("assets", "assets.assets_id=assetMaintenanceSchedules.assets_id", "LEFT");
        $this->DBLIB->where("assets.assets_deleted", 0);

        $schedule = $this->DBLIB->getOne('assetMaintenanceSchedules', [
            'assetMaintenanceSchedules.*',
            'assets.assets_tag'
        ]);

        if (!$schedule) {
            return false; // Schedule not found or not eligible for job creation
        }

        // Check if schedule is overdue
        if (strtotime($schedule['assetMaintenanceSchedules_nextDue']) > time()) {
            return false; // Not overdue yet
        }

        // Check if we already created a job for this schedule
        if (
            $schedule['assetMaintenanceSchedules_lastJobCreated'] !== null &&
            strtotime($schedule['assetMaintenanceSchedules_jobCreatedDate']) >= strtotime($schedule['assetMaintenanceSchedules_nextDue'])
        ) {
            return $schedule['assetMaintenanceSchedules_lastJobCreated']; // Job already exists
        }

        // Create the job
        return $this->createMaintenanceJob($schedule);
    }

    /**
     * Create a maintenance job for a specific schedule
     * @param array $schedule Schedule data including asset info
     * @return int|false The created job ID or false on failure
     */
    private function createMaintenanceJob($schedule)
    {
        $jobData = [
            'instances_id' => $schedule['instances_id'],
            'maintenanceJobs_assets' => $schedule['assets_id'],
            'maintenanceJobs_title' => $schedule['assetMaintenanceSchedules_type'] . " - " . $schedule['assets_tag'],
            'maintenanceJobs_faultDescription' => "Scheduled " . $schedule['assetMaintenanceSchedules_type'] . " due on " . date('d/m/Y', strtotime($schedule['assetMaintenanceSchedules_nextDue'])),
            'maintenanceJobs_timestamp_added' => date('Y-m-d H:i:s'),
            'maintenanceJobs_timestamp_due' => $schedule['assetMaintenanceSchedules_nextDue'],
            'maintenanceJobs_user_creator' => 1, // System user
            'maintenanceJobs_priority' => 5,
            'maintenanceJobs_blockAssets' => $schedule['assetMaintenanceSchedules_blockWhenOverdue'],
            'maintenanceJobs_flagAssets' => 0,
            'assetMaintenanceSchedules_id' => $schedule['assetMaintenanceSchedules_id'],
        ];

        $jobId = $this->DBLIB->insert("maintenanceJobs", $jobData);

        if ($jobId) {
            // Update schedule to track this job
            $this->DBLIB->where('assetMaintenanceSchedules_id', $schedule['assetMaintenanceSchedules_id']);
            $this->DBLIB->update('assetMaintenanceSchedules', [
                'assetMaintenanceSchedules_lastJobCreated' => $jobId,
                'assetMaintenanceSchedules_jobCreatedDate' => date('Y-m-d H:i:s'),
            ]);

            $this->bCMS->auditLog(
                "SCHEDULED-MAINTENANCE-JOB-CREATED",
                "maintenanceJobs",
                "Auto-created from schedule " . $schedule['assetMaintenanceSchedules_id'],
                1, // System user
                null,
                null,
                $jobId
            );
        }

        return $jobId;
    }
}