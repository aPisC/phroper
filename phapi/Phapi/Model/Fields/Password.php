<?php

namespace Phapi\Model\Fields;

use Exception;

class Password extends Text {
  public function __construct(array $data = null) {
    parent::__construct([
      "regex" => "^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{8,}$",
      "required",
      "type" => "password",
      "sql_type" => "VARCHAR(255)",
      "private" => true,
    ]);
    $this->updateData($data);
  }

  public function onSave($value) {
    if (!$value) return IgnoreField::instance();

    $value = parent::onSave($value);
    if ($value instanceof Exception) return $value;

    return password_hash($value, PASSWORD_DEFAULT);
  }
}
