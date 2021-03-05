<?php

namespace Model\Fields;

abstract class Field {
  private bool $private = false;
  private bool $readonly = false;
  private $field = null;

  public function __construct(array $data = null) {
    if (!$data) return;
    if (isset($data["private"])) $this->private = $data["private"];
    if (isset($data["readonly"])) $this->readonly = $data["readonly"];
    if (isset($data["field"])) $this->field = $data["field"];
  }

  public function getSQLType() {
    return null;
  }

  public function getFieldName($default) {
    return $this->field == null ? $default : $this->field;
  }

  public function isPrivate() {
    return $this->private;
  }

  public function isReadonly() {
    return $this->readonly;
  }

  public function forceUpdate() {
    return false;
  }

  public function hasDefault() {
    return false;
  }

  public function getDefault() {
    return null;
  }

  public function savedValue($value) {
    return $value;
  }

  public function readValue($value) {
    return $value;
  }
}
