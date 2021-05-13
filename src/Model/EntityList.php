<?php

namespace Phroper\Model;

use ArrayAccess;
use ArrayIterator;
use ArrayObject;
use Exception;
use IteratorAggregate;
use Phroper\Fields\IgnoreField;
use Phroper\Model;

class EntityList  extends Entity implements IteratorAggregate, ArrayAccess {

  protected array $values = array();
  private ?Model $model;

  public function __construct(?Model $model, ?array $values = null) {
    parent::__construct($model);
    if (is_array($values)) {
      foreach ($values as $v) {
        if (!($v  instanceof Entity))
          throw new Exception("EntityList must be initialized with Entities");
      }
      $this->values = $values;
    }
  }

  public function offsetSet($offset, $value): void {
    if (!($value  instanceof Entity))
      throw new Exception("EntityList only accepts with Entities");
    $this->values[$offset] = $value;
  }

  public function offsetExists($offset): bool {
    return array_key_exists($offset, $this->values);
  }

  public function offsetUnset($offset): void {
    unset($this->array[$offset]);
  }

  public function offsetGet($offset) {
    $val = $this->values[$offset];
    if ($val instanceof LazyResult) return $val->get();
    return $val;
  }
  public function count(): int {
    return count($this->values);
  }

  public function sanitizeEntity(): array {
    return $this->map(fn ($e) =>  $e->sanitizeEntity());
  }

  public function getIterator(): ArrayIterator {
    return (new ArrayObject($this->values))->getIterator();
  }

  public function map($callback): array {
    return array_map($callback, $this->values);
  }
}
