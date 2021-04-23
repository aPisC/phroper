<?php


namespace Phroper\Fields;

class Timestamp extends Field {
  public function __construct(array $data = null) {
    parent::__construct([
      "type" => "timestamp",
      "sql_type" => 'TIMESTAMP',
      "sql_extra" => 'DEFAULT NULL',
    ]);
    $this->updateData($data);
  }
  public function onSave($value) {
    if (!$value) $value == null;

    if ($value && is_numeric($value))
      $value =  date("Y-m-d H:i:s", $value);

    return parent::onSave($value);
  }
}
