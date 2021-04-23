<?php

namespace Phroper\Model;

class LazyResult {
  private $_get;
  public function __construct($get) {
    $this->_get = $get;
  }

  public function get() {
    $_get = $this->_get;
    if (is_callable($_get)) {
      $val = $_get();
      if ($val instanceof LazyResult) {
        $val = $val->get();
      }
      $this->_get = $val;
      return $val;
    }
    return $_get;
  }
}
