<?php

namespace QueryBuilder;

use Exception;

class QB_Const {
  public $value;

  public function __construct($value) {
    $this->value = $value;
  }

  public function getResolved() {
    if ($this->value === true) return "TRUE";
    if ($this->value === false) return "FALSE";
    if ($this->value === null) return "NULL";

    if (is_string($this->value)) return "\"" . addslashes($this->value) . "\"";
    if (is_double($this->value) || is_integer($this->value)) return strval($this->value);

    throw new Exception("QB_Const must be scalar");
  }
}
