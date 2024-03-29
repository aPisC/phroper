<?php

namespace Phroper\Fields;

class CreatedAt extends Timestamp {
  public function __construct($data = null) {
    parent::__construct([
      "sql_field" => "created_at",
      "type" => "timestamp",
      "auto" => true,
      "readonly" => true,
      "listed" => false,
      "sql_type" => "TIMESTAMP",
      "sql_extra" => "DEFAULT CURRENT_TIMESTAMP",
    ]);
    $this->updateData($data);
  }


  public function onSave($value) {
    return null;
  }
}
