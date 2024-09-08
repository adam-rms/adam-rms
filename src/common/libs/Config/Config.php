<?php
require_once __DIR__ . '/configStructureArray.php';

class ConfigValueNotSet extends Exception
{
}

class Config
{
  protected MysqliDb $DBLIB;
  public array $CONFIG_STRUCTURE;
  public array $CONFIG_MISSING_VALUES;
  protected array $DBCACHE;
  public function __construct()
  {
    global $DBLIB, $configStructureArray;
    $this->DBLIB = $DBLIB;
    $this->CONFIG_STRUCTURE = $configStructureArray;
    $this->CONFIG_MISSING_VALUES = [];
    $this->_setupCache();
  }
  protected function _setupCache()
  {
    /**
     * Download all the config values that are not special requests, and cache them to improve performance by consolidating to one query
     */
    $this->DBCACHE = [];
    $downloadNow = [];
    foreach ($this->CONFIG_STRUCTURE as $key => $value) {
      if ($value['specialRequest'] === false) {
        $downloadNow[] = $key;
      }
    }
    $this->DBLIB->where("config_key", $downloadNow, "IN");
    try {
      $downloadNowResults = $this->DBLIB->get("config", null, ["config_key", "config_value"]);
    } catch (Exception $e) {
      // TODO use twig for this
      if (getenv('DEV_MODE') == "true") {
          echo "Could not connect to database: " . $e->getMessage() . "\n\n\nPlease doulbe check you have setup environment variables correctly for the database connection.";
          exit;
      } else {
          echo "Could not connect to database";
          exit;
      }
    }
    foreach ($downloadNowResults as $key => $value) {
      $this->DBCACHE[$value['config_key']] = $value['config_value'];
    }
    foreach ($downloadNow as $value) {
      if (!isset($this->DBCACHE[$value])) {
        try {
          $this->DBCACHE[$value] = $this->_checkDefaults($value);
        } catch (ConfigValueNotSet) {
          $this->CONFIG_MISSING_VALUES[] = $value;
        }
      }
    }
    $this->DBCACHE['DEV'] = (getenv('DEV_MODE') == "true" ? true : false); //This is a bit of a special case, as it will always pull from ENV
  }
  public function getConfigArray()
  {
    return $this->DBCACHE;
  }
  public function get($key)
  {
    if (isset($this->DBCACHE[$key])) return $this->DBCACHE[$key];
    if (!isset($this->CONFIG_STRUCTURE[$key])) throw new Exception("Unknown config key presented");
    
    $this->DBLIB->where("config_key", $key);
    $value = $this->DBLIB->getValue("config", "config_value");
    if ($value === false or $value === null) {
      try {
        $value = $this->_checkDefaults($key);
      } catch (ConfigValueNotSet) {
        $value = false;
      }
      return $value;
    } else {
      $this->DBCACHE[$key] = $value;
      return $value;
    }
  }

  protected function _checkDefaults($key)
  {
    if (
      $this->CONFIG_STRUCTURE[$key]['envFallback'] !== false and
      $this->CONFIG_STRUCTURE[$key]['envFallback'] !== null and
      getenv($this->CONFIG_STRUCTURE[$key]['envFallback']) !== false and
      strlen(getenv($this->CONFIG_STRUCTURE[$key]['envFallback'])) >= $this->CONFIG_STRUCTURE[$key]['form']['minlength'] and
      strlen(getenv($this->CONFIG_STRUCTURE[$key]['envFallback'])) <= $this->CONFIG_STRUCTURE[$key]['form']['maxlength']
    )
      return getenv($this->CONFIG_STRUCTURE[$key]['envFallback']); // Use the environment variable if it's set and not empty
    else if ($this->CONFIG_STRUCTURE[$key]['default'] !== false) return $this->CONFIG_STRUCTURE[$key]['default'];
    else throw new ConfigValueNotSet("No value set for required config key $key");
  }

  public function formArrayBuild()
  {
    $formArray = [];
    foreach ($this->CONFIG_STRUCTURE as $key => $value) {
      $formArray[$key] = [
        "form" => $value['form'],
      ];
      $formArray[$key]['form']['default'] = $value['form']['default']();
      try {
        $formArray[$key]['value'] = $this->get($key);
      } catch (ConfigValueNotSet) {
        $formArray[$key]['value'] = null;
      }
    }
    return $formArray;
  }
  public function formArrayProcess($formInput)
  {
    $changesToMake = [];
    $errors = [];
    foreach ($this->CONFIG_STRUCTURE as $key => $value) {
      if (isset($formInput[$key])) {
        try {
          $currentValue = $this->get($key);
        } catch (ConfigValueNotSet) {
          $currentValue = null;
        }
        if ($formInput[$key] !== $currentValue) {
          unset($matchVerify);
          if ($value['form']['required'] || strlen($formInput[$key]) > 0) {
            // Only run validation if this is a required field
            if (strlen($formInput[$key]) < $value['form']['minlength']) {
              $errors[$key] = "Value too short";
              continue;
            } else if (strlen($formInput[$key]) > $value['form']['maxlength']) {
              $errors[$key] = "Value too long";
              continue;
            }
            $matchVerify = $value['form']['verifyMatch']($formInput[$key], $value['form']['options']);
            if (!$matchVerify['valid']) {
              $errors[$key] = $matchVerify['error'];
              continue;
            }
          }
          if (isset($matchVerify)) $changesToMake[] = ["config_key" => $key, "config_value" => $matchVerify['value']];
          else $changesToMake[] = ["config_key" => $key, "config_value" => $formInput[$key]];
        }
      } else if ($value['form']['required']) {
        $errors[$key] = "Value required";
      }
    }
    if (count($errors) > 0) return $errors;
    foreach ($changesToMake as $key => $value) {
      $update = $this->DBLIB->replace("config", $value);
      if (!$update) throw new Exception("Failed to update config value in database");
    }
    $this->_setupCache();
    return true;
  }
}
