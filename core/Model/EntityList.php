<?php

namespace Model;

class EntityList extends Entity {
  public function __construct($entities) {
    parent::__construct(null);
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

  public function map($callback) {
    return array_map($callback, $this->values);
  }
}
