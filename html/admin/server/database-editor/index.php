<?php

function adminer_object()
{

  class AdminerSoftware extends Adminer
  {

    function name()
    {
      // custom name in title and heading
      return 'AdamRMS Database Editor';
    }

    function credentials()
    {
      return array(getenv('bCMS__DB_HOSTNAME'), $_GET["username"], get_password());
    }

    function database()
    {
      return getenv('bCMS__DB_DATABASE');
    }

    function login($login, $password)
    {
      if (getenv('bCMS__DEV_MODE_DB_EDITOR') == true) return true;
      else return false;
    }
    function loginForm()
    {
      echo '<p>The username is <b>user</b> and password is <b>pass</b> for devcontainer installations. This editor should only be used for developing AdamRMS, not where real user data is being used.</p>';
      echo '<input type="hidden" name="auth[driver]" value="server"><input type="text" name="auth[username]" id="username" autocomplete="off" value="user"><input type="password" name="auth[password]" value="pass" autocomplete="off">';
      echo "<input type='submit' value='Login'>";
    }
    function tableName($tableStatus)
    {
      // tables without comments would return empty string and will be ignored by Adminer
      return h(ucwords(implode(' ', preg_split('/(?=[A-Z])/', $tableStatus["Name"]))));
    }
    function fieldName($field, $order = 0)
    {
      return h(ucwords(preg_replace('~\s+\[.*\]$~', '', str_replace('_', ' ', $field["field"]))));
    }
  }

  return new AdminerSoftware;
}
define('RUN_ADMINER', true); 
require_once __DIR__ . '/editor-4.8.1-mysql-en.php';
