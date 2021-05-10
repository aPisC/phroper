<?php


namespace Phroper\Fields;

class Date extends Field {
  public function __construct(array $data = null) {
    parent::__construct([
      "type" => "date",
      "sql_type" => "DATE"
    ]);
    $this->updateData($data);
  }
  public function onSave($value) {
    if (!$value) $value == null;

    if ($value && is_numeric($value))
      $value =  date("Y-m-d", $value);

    return parent::onSave($value);
  }
}
