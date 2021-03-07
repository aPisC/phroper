<?php

namespace Model;

class LazyResult {
  private $_get;
  public function __construct($get) {
    $this->_get = $get;
  }

  public function get() {
    $_get = $this->_get;
    if (is_callable($_get)) {
      $val = $_get();
      if ($val instanceof LazyResult)
        $val = $val->get();
      return $val;
    }
    return $_get;
  }
}
