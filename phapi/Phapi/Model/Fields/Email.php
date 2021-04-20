<?php

namespace Phapi\Model\Fields;

use Exception;

class Email extends Text {
  public function __construct($data = null) {
    parent::__construct($data);
    $this->updateData(["type" => "email"]);
  }

  public function onSave($value) {
    $value = parent::onSave($value);
    if ($value instanceof Exception) return $value;
    if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL))
      return new Exception("Email format is invalid");
    return $value;
  }
}
