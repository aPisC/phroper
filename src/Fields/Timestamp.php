<?php


namespace Phroper\Fields;

class Timestamp extends Field {
  public function __construct(array $data = null) {
    parent::__construct([
      "type" => "timestamp",
      "sql_type" => 'TIMESTAMP',
      "sql_extra" => "DEFAULT NULL"
    ]);
    $this->updateData($data);
  }

  public function onLoad($value, $key, $assoc, $populates) {
    $value = parent::onLoad($value, $key, $assoc, $populates);
    if (!$value) return null;
    return date(DATE_ISO8601, strtotime($value));
  }

  public function onSave($value) {
    if (!$value) $value == null;
    if (is_string($value)) $value = strtotime($value);

    if ($value && is_numeric($value))
      $value =  date("Y-m-d H:i:s.u", $value);

    return parent::onSave($value);
  }
}
