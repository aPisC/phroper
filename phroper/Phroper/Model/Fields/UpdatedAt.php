<?php

namespace Phroper\Model\Fields;

class UpdatedAt extends Timestamp {
  public function __construct() {
    parent::__construct([
      "sql_field" => "updated_at",
      "type" => "timestamp",
      "auto" => true,
      "readonly" => true,
      "forced" => false,
      "sql_type" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ]);
  }

  public function onSave($value) {
    return null;
  }
}
