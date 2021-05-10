<?php


namespace Phroper\Fields;

class Datetime extends Field {
  public function __construct(array $data = null) {
    parent::__construct([
      "type" => "datetime",
      "sql_type" => "DATETIME"
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
