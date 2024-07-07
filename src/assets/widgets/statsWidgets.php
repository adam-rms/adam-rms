<?php
use Money\Currency;
use Money\Money;
/*
 * HOW TO ADD A NEW STATS WIDGET
 * - Duplicate & edit one of the .twig files (maintenanceOutstanding.twig is a good one to start)
 * - Create a class for it here - whatever name you give the class set in the top of the twig file
 * - Add it to the "$widgetsList" variable at the top of this class
 * - Add it to instances/instances_stats.twig
 */
class statsWidgets
{
    /*
     * Designed to be accessed by Twig
     */
    protected $widgetsArray = [];
    protected $userWidgetsExcludeArray = [];
    protected $showAll = false;
    protected $widgetsList = ["inventoryValueGraph","inventoryTotal","storageUsage","userCount","maintenanceOutstanding","myMaintenance"];
    public function __construct($userWidgetsExcludeArray = [],$showAll=false) {
        $this->showAll = $showAll;
        foreach ($userWidgetsExcludeArray as $widgetName) {
            //Get a list of widgets the user wants to hide from the dashboard
            if (method_exists($this, $widgetName)) $this->userWidgetsExcludeArray[] = $widgetName;
        }
        foreach ($this->widgetsList as $widgetName) {
            //Add all the requested widgets
            if (method_exists($this, $widgetName) and (!in_array($widgetName,$this->userWidgetsExcludeArray) or $showAll)) $this->widgetsArray[] = $widgetName;
        }
    }
    public function getAllDashboard() {
        return $this->widgetsArray;
    }
    public function checkUser($widgetName) {
        if (in_array($widgetName, $this->userWidgetsExcludeArray)) return false;
        else return true;
    }
    public function widget($widgetName, $arguments = []) {
        if(method_exists($this, $widgetName)) return $this->$widgetName($arguments);
        else return false;
    }

    /*
     * ACTUAL WIDGETS GO BELOW - TWIG CALLS EM
     */
    private function userCount($arguments = []) {
        global $DBLIB;
        if (!$arguments['instanceid']) return [];

        $DBLIB->join("instancePositions","instancePositions.instancePositions_id=userInstances.instancePositions_id","LEFT");
        $DBLIB->where("instancePositions.instances_id", $arguments['instanceid']);
        $DBLIB->where("userInstances.userInstances_deleted",  0);
        $DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
        return ["COUNT" => $DBLIB->getValue ("userInstances", "count(*)")];
    }
    private function inventoryValueGraph($arguments = []) {
        global $DBLIB,$AUTH;
        if (!$arguments['instanceid']) return [];

        $DBLIB->where("assets.instances_id", $arguments['instanceid']);
        $DBLIB->orderBy("assets_inserted", "ASC");
        $DBLIB->where("assets_deleted", 0);
        $DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
        $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
        $assets= $DBLIB->get("assets", null, ["assets_inserted", "assets_value", "assetTypes_value", "assetTypes_mass"]);
        if (!$assets) return [];

        $timeSeriesDataValue = [];
        $timeSeriesDataMass = [];
        $first = $assets[0];
        $last = end($assets);
        reset($assets);

        $difference = (strtotime($last["assets_inserted"])-strtotime($first['assets_inserted']));
        /*
         * Get first and last element to decide how much to break down the data - we're aiming for upto 30 data points
         */

        if ((10*365*24*3600) < $difference) { //10 years
            //Years
            $timeSeries = function ($date) {
                return date("Y", strtotime($date));
            };
        } elseif (94608000 < $difference) {//3 years
            //Quarters
            $timeSeries = function ($date) {
                $quarter = ceil(date("n", strtotime($date)) / 3);
                $year = date("Y", strtotime($date));
                return "Q" . $quarter . " " . $year;
            };
        } elseif (31536000 < $difference) { //1 year
            //Months
            $timeSeries = function ($date) {
                return date("M Y", strtotime($date));
            };
        } else {
            //Weeks
            $timeSeries = function ($date) {
                return "w" . date("W Y", strtotime($date));
            };
        }

        $totalValue = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
        foreach ($assets as $asset) {
            $asset['timeSeries'] = $timeSeries($asset['assets_inserted']);
            if (!isset($timeSeriesDataValue[$asset['timeSeries']])) $timeSeriesDataValue[$asset['timeSeries']] = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
            $timeSeriesDataValue[$asset['timeSeries']] = $totalValue->add(new Money(($asset['assets_value'] === null ? $asset['assetTypes_value'] : $asset['assets_value']),new Currency($AUTH->data['instance']['instances_config_currency'])));
            $totalValue=$timeSeriesDataValue[$asset['timeSeries']];
            if (!isset($timeSeriesDataMass[$asset['timeSeries']])) $timeSeriesDataMass[$asset['timeSeries']] = 0.0;
            $timeSeriesDataMass[$asset['timeSeries']] += $asset['assetTypes_mass'];
        }

        return ["timeSeriesValue" => $timeSeriesDataValue, "timeSeriesMass" => $timeSeriesDataMass];
    }
    private function inventoryTotal($arguments = []) {
        global $DBLIB,$AUTH;
        if (!$arguments['instanceid']) return [];

        $DBLIB->where("assets.instances_id", $arguments['instanceid']);
        $DBLIB->orderBy("assets_inserted", "ASC");
        $DBLIB->where("assets_deleted", 0);
        $DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
        $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
        $assets= $DBLIB->get("assets", null, ["assets_inserted", "assets_value","assetTypes_value", "assetTypes_mass"]);
        if (!$assets) return [];

        $return = ["VALUE" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),"MASS" => 0.0];
        foreach ($assets as $asset) {
            $return['VALUE'] = $return['VALUE']->add(new Money(($asset['assets_value'] === null ? $asset['assetTypes_value'] : $asset['assets_value']),new Currency($AUTH->data['instance']['instances_config_currency'])));
            $return['MASS'] += $asset['assetTypes_mass'];
        }
        return $return;
    }
    private function storageUsage($arguments = []) {
        global $DBLIB, $bCMS;
        if (!$arguments['instanceid']) return [];

        $return = ["USED" => 0.0, "CAPACITY" => 0];

        $DBLIB->where("instances_id", $arguments['instanceid']);
        $return['CAPACITY'] = $DBLIB->getvalue("instances", "instances_storageLimit");

        $return['USED'] = $bCMS->s3StorageUsed($arguments['instanceid']);
        return $return;
    }
    private function maintenanceOutstanding($arguments = []) {
        global $DBLIB;
        if (!$arguments['instanceid']) return [];
        if (!$arguments['userid']) return [];

        $DBLIB->where("maintenanceJobs.instances_id", $arguments['instanceid']);
        $DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
        $DBLIB->join("maintenanceJobsStatuses", "maintenanceJobs.maintenanceJobsStatuses_id=maintenanceJobsStatuses.maintenanceJobsStatuses_id", "LEFT");
        $DBLIB->where("(maintenanceJobsStatuses.maintenanceJobsStatuses_showJobInMainList = 1 OR maintenanceJobs.maintenanceJobsStatuses_id IS NULL)");
        $totalJobCount = $DBLIB->getValue("maintenanceJobs", "COUNT(*)");

        $DBLIB->where("maintenanceJobs.instances_id", $arguments['instanceid']);
        $DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
        $DBLIB->join("maintenanceJobsStatuses", "maintenanceJobs.maintenanceJobsStatuses_id=maintenanceJobsStatuses.maintenanceJobsStatuses_id", "LEFT");
        $DBLIB->where("(maintenanceJobsStatuses.maintenanceJobsStatuses_showJobInMainList = 1 OR maintenanceJobs.maintenanceJobsStatuses_id IS NULL)");
        $DBLIB->where("maintenanceJobs_user_assignedTo",$arguments['userid']);
        $myJobCount = $DBLIB->getValue("maintenanceJobs", "COUNT(*)");

        return ["TOTAL" => $totalJobCount, "MY" => $myJobCount];
    }
    private function myMaintenance($arguments = []) {
        global $DBLIB;
        if (!$arguments['instanceid']) return [];
        if (!$arguments['userid']) return [];

        $DBLIB->where("maintenanceJobs.instances_id", $arguments['instanceid']);
        $DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
        $DBLIB->join("maintenanceJobsStatuses", "maintenanceJobs.maintenanceJobsStatuses_id=maintenanceJobsStatuses.maintenanceJobsStatuses_id", "LEFT");
        $DBLIB->where("(maintenanceJobsStatuses.maintenanceJobsStatuses_showJobInMainList = 1 OR maintenanceJobs.maintenanceJobsStatuses_id IS NULL)");
        $DBLIB->where("maintenanceJobs_user_assignedTo",$arguments['userid']);
        $DBLIB->orderBy("maintenanceJobsStatuses.maintenanceJobsStatuses_order", "ASC");
        $DBLIB->orderBy("maintenanceJobs.maintenanceJobs_priority", "ASC");
        $DBLIB->orderBy("maintenanceJobs.maintenanceJobs_timestamp_due", "ASC");
        $DBLIB->orderBy("maintenanceJobs.maintenanceJobs_timestamp_added", "ASC");
        $jobs = $DBLIB->get('maintenanceJobs', null, ["maintenanceJobs.*", "maintenanceJobsStatuses.maintenanceJobsStatuses_name"]);
        return $jobs;
    }
}