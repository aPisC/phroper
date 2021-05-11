<?php

namespace Phroper\Fields;

class UpdatedAt extends Timestamp {
  public function __construct() {
    parent::__construct([
      "sql_field" => "updated_at",
      "type" => "timestamp",
      "auto" => true,
      "readonly" => true,
      "forced" => false,
      "sql_type" => "TIMESTAMP",
      "sql_extra" => "DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
      "listed" => false,
    ]);
  }

  public function onSave($value) {
    return null;
  }
}
