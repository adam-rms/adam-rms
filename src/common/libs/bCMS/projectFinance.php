<?php

use Money\Money;
use Money\Currency;

class projectFinance
{
  public function durationMathsByDates($start, $end)
  {
    $start = strtotime(date("d F Y 00:00:00", strtotime($start)));
    $end = strtotime(date("d F Y 23:59:59", strtotime($end)));
    $diff = ceil(($end - $start) / 86400);
    if ($diff < 1) $diff = 1;
    return ["days" => $diff, "weeks" => 0, "calendarDays" => $diff];
  }
  public function durationMaths($projects_id)
  {
    global $DBLIB;
    $DBLIB->where("projects_id", $projects_id);
    $project = $DBLIB->getone("projects", ["projects_dates_finances_days", "projects_dates_finances_weeks", "projects_dates_deliver_start", "projects_dates_deliver_end"]);
    if (!$project) return false;

    if ($project['projects_dates_finances_days'] !== NULL and $project['projects_dates_finances_weeks'] !== NULL) {
      $rawDays = $this->durationMathsByDates($project['projects_dates_deliver_start'], $project['projects_dates_deliver_end']);
      return ["days" => $project['projects_dates_finances_days'], "weeks" => $project['projects_dates_finances_weeks'], "calendarDays" => $rawDays['days']];
    } else {
      return $this->durationMathsByDates($project['projects_dates_deliver_start'], $project['projects_dates_deliver_end']);
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
      //It's a money object!
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
