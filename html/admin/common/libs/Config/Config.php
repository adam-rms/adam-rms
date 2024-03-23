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
    $downloadNowResults = $this->DBLIB->get("config", null, ["config_key", "config_value"]);
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
  }
  public function getConfigArray()
  {
    return $this->DBCACHE;
  }
  public function get($key)
  {
    if (!isset($this->CONFIG_STRUCTURE[$key])) throw new Exception("Unknown config key presented");
    if (isset($this->DBCACHE[$key])) return $this->DBCACHE[$key];

    $this->DBLIB->where("config_key", $key);
    $value = $this->DBLIB->getValue("config", "config_value");
    if ($value === false or $value === null) {
      try {
        $value = $this->_checkDefaults($key);
      } catch (ConfigValueNotSet) {
        throw new Exception("No value set for $key");
      }
      if ($this->CONFIG_STRUCTURE[$key]["default"] === false) return false;
      else return $this->CONFIG_STRUCTURE[$key]["default"];
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
}
