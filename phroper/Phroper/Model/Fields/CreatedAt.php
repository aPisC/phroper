<?php

namespace Phroper\Model\Fields;

class CreatedAt extends Timestamp {
  public function __construct() {
    parent::__construct([
      "field" => "created_at",
      "type" => "timestamp",
      "auto" => true,
      "readonly" => true,
      "sql_type" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ]);
  }


  public function onSave($value) {
    return null;
  }
}
