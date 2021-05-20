<?php


namespace Phroper\Fields;

use Exception;

class Date extends Field {
  public function __construct(array $data = null) {
    parent::__construct([
      "type" => "date",
      "sql_type" => "DATE",
      "sql_extra" => "DEFAULT NULL"
    ]);
    $this->updateData($data);
  }

  public function onLoad($value, $key, $assoc, $populates) {
    $value = parent::onLoad($value, $key, $assoc, $populates);
    if (!$value) return null;
    return date("Y-m-d", strtotime($value));
  }

  public function onSave($value) {
    if (!$value) $value == null;
    if (is_string($value))
      $value = strtotime(substr(trim($value), 0, 10));

    if ($value && is_numeric($value))
      $value =  date("Y-m-d", $value);

    return parent::onSave($value);
  }
}
