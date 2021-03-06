<?php

namespace QueryBuilder;

class QBModificationCollector {
  private array $usedFields = [];
  private array $values = [
    [],
    []
  ];

  public function __construct() {
  }
  public function setValue($key, $value) {
    if (!in_array($key, $this->usedFields))
      $this->usedFields[] = $key;
    $this->values[count($this->values) - 1][$key] = $value;
  }
  public function next() {
    $this->values[] = [];
  }
  public function setDefaultValue($key, $value) {
    if (!in_array($key, $this->usedFields))
      $this->usedFields[] = $key;
    $this->values[0][$key] = $value;
  }
  public function getValue($key, $id = 0) {
    if ($id >= 0 && isset($this->values[$id + 1][$key]))
      return $this->values[$id + 1][$key];
    if (isset($this->values[0][$key]))
      return $this->values[0][$key];
    return null;
  }
  public function getFields() {
    return $this->usedFields;
  }
  public function getEntityCount() {
    return count($this->values) - 1;
  }
}
