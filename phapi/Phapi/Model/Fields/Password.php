<?php

namespace Phapi\Model\Fields;

class Password extends Field {
  public function __construct(array $data = null) {
    parent::__construct($data);
    $this->updateData([
      "type" => "password",
      "sql_type" => "VARCHAR(255)",
      "private" => true,
    ]);
  }

  public function onSave($value) {
    $value = parent::onSave($value);
    if (!$value) return IgnoreField::instance();
    return password_hash($value, PASSWORD_DEFAULT);
  }
}
