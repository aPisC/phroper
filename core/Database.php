<?php

class Database {
  private static ?mysqli $_instance = null;
  public static function instance() {
    if (self::$_instance == null)
      self::$_instance = new mysqli(Config::$database['server'], Config::$database['user'], Config::$database['password'], Config::$database['database'], Config::$database['port']);
    return self::$_instance;
  }
}
