<?php

namespace Phapi\Model\Fields;

use Exception;

class Email extends Text {
  public function __construct($data = null) {
    parent::__construct(["type" => "email"]);
    $this->updateData($data);
  }

  public function onSave($value) {
    $value = parent::onSave($value);
    if ($value instanceof Exception) return $value;
    if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL))
      return new Exception("Email format is invalid");
    return $value;
  }
}
