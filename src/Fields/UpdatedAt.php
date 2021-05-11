<?php

namespace Phroper\Fields;

class UpdatedAt extends Timestamp {
  public function __construct($data = null) {
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
    $this->updateData($data);
  }

  public function onSave($value) {
    return null;
  }
}
