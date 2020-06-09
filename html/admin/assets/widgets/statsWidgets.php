<?php
use Money\Currency;
use Money\Money;
class statsWidgets
{
    /*
     * Designed to be accessed by Twig
     */
    protected $widgetsArray = [];
    public function __construct($widgetsArray = []) {
        foreach ($widgetsArray as $widgetName) {
            //Add all the requested widgets
            if (method_exists($this, $widgetName)) $this->widgetsArray[] = $widgetName;
        }
    }
    public function getAllUser() {
        return $this->widgetsArray;
    }
    public function checkUser($widgetName) {
        $key = array_search($widgetName, $this->widgetsArray);

        if ($key !== false) return true;
        else return false;
    }
    public function widget($widgetName, $arguments = []) {
        if(method_exists($this, $widgetName)) return $this->$widgetName($arguments);
        else return false;
    }

    /*
     * ACTUAL WIDGETS GO BELOW - TWIG CALLS EM
     */
    private function upcomingEventsCount($arguments = []) {

    }
    private function inventoryValueGraph($arguments = []) {
        global $DBLIB,$AUTH;
        if (!$arguments['instanceid']) return [];

        $DBLIB->where("assets.instances_id", $arguments['instanceid']);
        $DBLIB->orderBy("assets_inserted", "ASC");
        $DBLIB->where("assets_deleted", 0);
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
        global $DBLIB;
        if (!$arguments['instanceid']) return [];

        $return = ["USED" => 0.0, "CAPACITY" => 0];

        $DBLIB->where("instances_id", $arguments['instanceid']);
        $return['CAPACITY'] = $DBLIB->getvalue("instances", "instances_storageLimit");

        $DBLIB->where("s3files.instances_id", $arguments['instanceid']);
        $DBLIB->where("(s3files_meta_deleteOn IS NULL)");
        $DBLIB->where("s3files_meta_physicallyStored", 1);
        $return['USED'] = $DBLIB->getValue("s3files", "SUM(s3files_meta_size)");
        return $return;
    }
}