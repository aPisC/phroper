<?php

namespace Phapi\Model\Fields;

class UpdatedAt extends Timestamp {
  public function __construct() {
    parent::__construct([
      "field" => "updated_at",
      "type" => "timestamp",
      "auto" => true,
      "readonly" => true,
      "forced" => true,
      "default" => function () {
        return time();
      },
      "sql_type" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ]);
  }

  public function onSave($value) {
    return parent::onSave(time());
  }
}
