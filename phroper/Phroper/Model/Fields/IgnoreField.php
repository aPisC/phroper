<?php

namespace Phroper\Model\Fields;

class IgnoreField {
  private static ?IgnoreField $_instance = null;
  public static function instance() {
    if (self::$_instance == null)
      self::$_instance = new IgnoreField();
    return self::$_instance;
  }
}
