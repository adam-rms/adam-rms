<?php

use Money\Money;
use Money\Currency;

class projectFinance
{
  /**
   * Parses a business's instances_dayRateMultipliers JSON into a list of ["from" => int, "to" => int, "multiplier" => numeric string]
   * Anything malformed is dropped, so a bad value can only ever fall back to charging one day rate per day
   */
  public static function parseDayRateMultipliers($json)
  {
    if ($json === null or $json === '') return [];
    $rules = json_decode($json, true);
    if (!is_array($rules)) return [];
    $parsed = [];
    foreach ($rules as $rule) {
      if (!is_array($rule) or !isset($rule['from'], $rule['to'], $rule['multiplier'])) continue;
      if (!is_numeric($rule['from']) or !is_numeric($rule['to']) or !is_numeric($rule['multiplier'])) continue;
      $parsed[] = ["from" => intval($rule['from']), "to" => intval($rule['to']), "multiplier" => (string) $rule['multiplier']];
    }
    return $parsed;
  }
  /**
   * The number of day rates to charge for $days days, from a business's multipliers (or null if none applies)
   */
  public static function dayRateMultiplierFor($days, $rules)
  {
    foreach ($rules as $rule) {
      if ($days >= $rule['from'] and $days <= $rule['to']) return $rule['multiplier'];
    }
    return null;
  }
  private function instanceDayRateMultipliers($instances_id)
  {
    global $DBLIB;
    if ($instances_id === null) return [];
    $DBLIB->where("instances_id", $instances_id);
    $instance = $DBLIB->getone("instances", ["instances_dayRateMultipliers"]);
    if (!$instance) return [];
    return self::parseDayRateMultipliers($instance['instances_dayRateMultipliers']);
  }
  /**
   * If the business ($instances_id) has a day rate multiplier for the number of days being charged, "days" becomes the number of
   * day rates to charge for them (which may not be a whole number), and "durationDays" keeps the number of days it was worked out from
   */
  private function applyDayRateMultiplier($maths, $instances_id)
  {
    $multiplier = self::dayRateMultiplierFor($maths['days'], $this->instanceDayRateMultipliers($instances_id));
    if ($multiplier === null) return $maths;
    return array_merge($maths, ["days" => $multiplier, "durationDays" => $maths['days'], "dayRateMultiplier" => $multiplier]);
  }
  public function durationMathsByDates($start, $end, $instances_id = null)
  {
    $start = strtotime(date("d F Y 00:00:00", strtotime($start)));
    $end = strtotime(date("d F Y 23:59:59", strtotime($end)));
    $diff = ceil(($end - $start) / 86400);
    if ($diff < 1) $diff = 1;
    return $this->applyDayRateMultiplier(["days" => $diff, "weeks" => 0, "calendarDays" => $diff], $instances_id);
  }
  /**
   * A custom duration: $days are the days the equipment is used for, so they go through the multipliers too, and weeks are charged a week rate each
   */
  public function durationMathsByCustom($days, $weeks, $calendarDays, $instances_id)
  {
    return $this->applyDayRateMultiplier(["days" => $days, "weeks" => $weeks, "calendarDays" => $calendarDays], $instances_id);
  }
  public function durationMaths($projects_id)
  {
    global $DBLIB;
    $DBLIB->where("projects_id", $projects_id);
    $project = $DBLIB->getone("projects", ["instances_id", "projects_dates_finances_days", "projects_dates_finances_weeks", "projects_dates_deliver_start", "projects_dates_deliver_end"]);
    if (!$project) return false;

    if ($project['projects_dates_finances_days'] !== NULL and $project['projects_dates_finances_weeks'] !== NULL) {
      $rawDays = $this->durationMathsByDates($project['projects_dates_deliver_start'], $project['projects_dates_deliver_end']);
      return $this->durationMathsByCustom($project['projects_dates_finances_days'], $project['projects_dates_finances_weeks'], $rawDays['days'], $project['instances_id']);
    } else {
      return $this->durationMathsByDates($project['projects_dates_deliver_start'], $project['projects_dates_deliver_end'], $project['instances_id']);
    }
  }
}
class projectFinanceCacher
{
  //This class assumes that the projectid has been validated as within the instance
  private $data, $projectid;
  private $changesMade = false;
  public function __construct($projectid)
  {
    global $AUTH;
    //Reset the data
    $this->projectid = $projectid;
    $this->data = [
      "projectsFinanceCache_equipmentSubTotal" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),
      "projectsFinanceCache_equiptmentDiscounts" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),
      "projectsFinanceCache_salesTotal" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),
      "projectsFinanceCache_staffTotal" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),
      "projectsFinanceCache_externalHiresTotal" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),
      "projectsFinanceCache_paymentsReceived" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),
      "projectsFinanceCache_value" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),
      "projectsFinanceCache_mass" => 0.0
    ];
  }
  public function save()
  {
    //Process the changes at the end of the script
    global $DBLIB;
    if ($this->changesMade) {
      $dataToUpload = [];
      foreach ($this->data as $key => $value) {
        //Put it into a format for mysql
        if ($key != 'projectsFinanceCache_mass') $value = $value->getAmount();
        if ($value != 0) $dataToUpload[$key] = $DBLIB->inc($value);
      }
      $dataToUpload['projectsFinanceCache_timestampUpdated'] = date("Y-m-d H:i:s");
      $dataToUpload['projectsFinanceCache_equiptmentTotal'] = $DBLIB->inc($this->data["projectsFinanceCache_equipmentSubTotal"]->subtract($this->data['projectsFinanceCache_equiptmentDiscounts'])->getAmount());
      $dataToUpload['projectsFinanceCache_grandTotal'] = $DBLIB->inc((($this->data["projectsFinanceCache_equipmentSubTotal"]->subtract($this->data['projectsFinanceCache_equiptmentDiscounts']))->add($this->data['projectsFinanceCache_salesTotal'], $this->data['projectsFinanceCache_staffTotal'], $this->data["projectsFinanceCache_externalHiresTotal"])->subtract($this->data['projectsFinanceCache_paymentsReceived']))->getAmount());
      $DBLIB->where("projects_id", $this->projectid);
      $DBLIB->orderBy("projectsFinanceCache_timestamp", "DESC");
      return $DBLIB->update("projectsFinanceCache", $dataToUpload, 1); //Update the most recent cache datapoint
    } else return true;
  }
  public function adjust($key, $value, $subtract = false)
  {
    if ($key == 'projectsFinanceCache_mass' and ($value !== 0 or $value !== null)) {
      $this->changesMade = true;
      if ($subtract) $value = -1 * $value;
      $this->data[$key] += $value;
    } else {
      $this->changesMade = true;
      //Ensure value is a Money object
      if (!($value instanceof Money)) {
        global $AUTH;
        $value = new Money($value, new Currency($AUTH->data['instance']['instances_config_currency']));
      }
      if ($subtract) {
        $this->data[$key] = $this->data[$key]->subtract($value);
      } else {
        $this->data[$key] = $this->data[$key]->add($value);
      }
    }
  }
  public function adjustPayment($paymentType, $value, $subtract = false)
  {
    switch ($paymentType) {
      case 1:
        $key = 'projectsFinanceCache_paymentsReceived';
        break;
      case 2:
        $key = 'projectsFinanceCache_salesTotal';
        break;
      case 3:
        $key = 'projectsFinanceCache_externalHiresTotal';
        break;
      case 4:
        $key = 'projectsFinanceCache_staffTotal';
        break;
      default:
        return false;
    }
    return $this->adjust($key, $value, $subtract);
  }
}
