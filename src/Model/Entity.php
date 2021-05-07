<?php

namespace Phroper\Model;

use ArrayAccess;
use Phroper\Model;

class Entity implements ArrayAccess {
  protected array $values = array();
  private ?Model $model;

  public function __construct(?Model $model, ?array $values = null) {
    $this->model = $model;
    if (is_array($values)) $this->values = $values;
  }

  public function offsetSet($offset, $value): void {
    $this->values[$offset] = $value;
  }

  public function offsetExists($offset): bool {
    return array_key_exists($offset, $this->values);
  }

  public function offsetUnset($offset): void {
    unset($this->array[$offset]);
  }

  public function offsetGet($offset): Entity|EntityList|array|int|float|string|bool|null {
    $val = $this->values[$offset];
    if ($val instanceof LazyResult) return $val->get();
    return $val;
  }

  public function sanitizeEntity(): array {
    return $this->model->sanitizeEntity($this);
  }
}
