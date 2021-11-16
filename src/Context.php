<?php

namespace Phroper;

class Context {
  private array $contextValues = [];

  public function set($name, $value) {
    $this->contextValues[$name] = $value;
  }

  public function get($name) {
    if (!isset($this->contextValues[$name]))
      return null;
    return $this->contextValues[$name];
  }
}
