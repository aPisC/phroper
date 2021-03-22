<?php

namespace Model\Fields;

class CreatedAt extends Timestamp {
  public function __construct() {
    parent::__construct(["field" => "created_at"]);
  }

  public function isReadonly() {
    return true;
  }

  public function getDefault() {
    return time();
  }

  function getSQLType() {
    return "TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
  }
}
