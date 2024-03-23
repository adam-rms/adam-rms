<?php

use Money\Money;
use Money\Currency;

class projectFinance
{
  public function durationMaths($projects_dates_deliver_start, $projects_dates_deliver_end)
  {
    //Calculate the default pricing for all assets
    $return = ["string" => "Calculated based on:", "days" => 0, "weeks" => 0];
    $start = strtotime(date("d F Y 00:00:00", strtotime($projects_dates_deliver_start)));
    $end = strtotime(date("d F Y 23:59:59", strtotime($projects_dates_deliver_end)));
    if (date("N", $start) == 6) {
      $return['weeks'] += 1;
      $return['string'] .= "\nBegins on Saturday so first weekend charged as one week";
      $start = $start + (86400 * 2);
    } elseif (date("N", $start) == 7) {
      $return['weeks'] += 1;
      $return['string'] .= "\nBegins on Sunday so first weekend charged as one week";
      $start = $start + 86400;
    }
    if (($end - $start) > 259200) { //If it's just one weekend it doesn't count as two weeks
      if (date("N", $end) == 6) {
        $return['weeks'] += 1;
        $return['string'] .= "\nEnds on Saturday so last weekend charged as one week";
        $end = $end - 86400;
      } elseif (date("N", $end) == 7) {
        $return['weeks'] += 1;
        $return['string'] .= "\nEnds on Sunday so last weekend charged as one week";
        $end = $end - (86400 * 2);
      }
    }

    $remaining = strtotime(date("d F Y 23:59:59", $end)) - strtotime(date("d F Y", $start));
    if ($remaining > 0) {
      $remaining = ceil($remaining / 86400); //Convert to days
      $weeks = floor($remaining / 7); //Number of week periods
      if ($weeks > 0) {
        $return['weeks'] += $weeks;
        if ($weeks == 1) {
          $return['string'] .= "\nAdd 1 week period to reflect a period of more than 7 days";
        } else {
          $return['string'] .= "\nAdd " . $weeks . " week periods to reflect a period of more than 7 days";
        }
        $remaining = $remaining - ($weeks * 7);
      }
      if ($remaining > 2) {
        $return['string'] .= "\nAdd a week to discount a period between 3 and 7 days";
        $return['weeks'] += 1;
        $remaining = $remaining - 7;
      }
      if ($remaining > 0) {
        $return['days'] += ceil($remaining);
        if ($remaining == 1) {
          $return['string'] .= "\nAdd 1 day period";
        } else {
          $return['string'] .= "\nAdd " . ceil($remaining) . " day periods";
        }
      }
    }
    return $return;
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
