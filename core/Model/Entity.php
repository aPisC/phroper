<?php

namespace Model;

use ArrayAccess;

class Entity implements ArrayAccess {
  private $values = array();

  public function __construct() {
  }

  public function offsetSet($offset, $value) {
    $this->values[$offset] = $value;
  }

  public function offsetExists($offset) {
    return array_key_exists($offset, $this->values);
  }

  public function offsetUnset($offset) {
    unset($this->array[$offset]);
  }

  public function offsetGet($offset) {
    $val = $this->values[$offset];
    if ($val instanceof LazyResult) return $val->get();
    return $val;
  }
}
