<?php

namespace Phroper;

use Exception;
use Throwable;

class Injector {
  private array $typeMap = [];
  private array $entityCache = [];

  public function tryInstantiate(string $type) {
    if (isset($this->entityCache[$type])) return $this->entityCache[$type];
    if (isset($this->typeMap[$type])) {
      $class = $this->typeMap[$type];
      $entity = new $class();
      $this->entityCache[$type] = $entity;
      return $entity;
    }

    throw new Exception("Type could not be injected: " . $type);
  }

  public function instantiate(string $type) {
    try {
      return $this->tryInstantiate($type);
    } catch (Throwable $ex) {
      return null;
    }
  }

  public function hasEntityCached($type) {
    return isset($this->entityCache[$type]);
  }

  public function hasType($type) {
    return isset($this->entityCache[$type]) || isset($this->typeMap[$type]);
  }

  public function listTypes() {
    return array_keys($this->typeMap);
  }

  public function provideType(string $type, mixed $constructor) {
    $this->typeMap[$type] = $constructor;
  }

  public function provideEntity(string $type, mixed $value) {
    $this->entityCache[$type] = $value;
  }
}
