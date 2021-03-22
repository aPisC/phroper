<?php

class Context {
  private static array $values = array();

  public static function get($name) {
    if (!isset(self::$values[$name]))
      return null;
    return self::$values[$name];
  }

  public static function set($name, $value) {
    self::$values[$name] = $value;
  }
}
