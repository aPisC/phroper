<?php


namespace Phapi\Model\Fields;

class Timestamp extends Field {
  public function __construct(array $data = null) {
    parent::__construct($data);
    $this->updateData([
      "type" => "timestamp",
      "sql_type" => 'TIMESTAMP NULL DEFAULT NULL',
    ]);
  }
  public function onSave($value) {
    if (is_numeric($value))
      return date("Y-m-d H:i:s", $value);
    return $value;
  }
}
