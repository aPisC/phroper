<?php

namespace Phroper\Model;

use ArrayObject;
use IteratorAggregate;

class EntityList extends Entity implements IteratorAggregate {
  public function __construct($entities = null) {
    parent::__construct(null);
    $this->values = [];
    if (is_array($entities)) $this->values = $entities;
  }

  public function count() {
    return count($this->values);
  }

  public function sanitizeEntity() {
    return array_map(function ($e) {
      if ($e instanceof Entity) return $e->sanitizeEntity();
      return $e;
    }, $this->values);
  }

  public function getIterator() {
    return (new ArrayObject($this->values))->getIterator();
  }

  public function map($callback) {
    return array_map($callback, $this->values);
  }
}
