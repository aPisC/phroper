<?php


namespace Phroper\Fields;

use Exception;

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
    if (is_string($value)) $value = strtotime($value);

    if ($value && is_numeric($value))
      $value =  date("Y-m-d H:i:s.u", $value);

    return parent::onSave($value);
  }
}
