<?php

namespace Phroper\Model;

use ArrayAccess;

class Entity implements ArrayAccess {
  protected $values = array();
  private $model;

  public function __construct($model) {
    $this->model = $model;
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

  public function sanitizeEntity() {
    return $this->model->sanitizeEntity($this);
  }
}
