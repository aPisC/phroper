<?php

namespace Phapi\Model\Fields;

class CreatedAt extends Timestamp {
  public function __construct() {
    parent::__construct([
      "field" => "created_at",
      "type" => "timestamp",
      "auto" => true,
      "readonly" => true,
      "default" => function () {
        return time();
      },
      "sql_type" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ]);
  }
}
