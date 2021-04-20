<?php

namespace Phapi\Model\Fields;

use Exception;

class Email extends Text {
  public function __construct($data = null) {
    parent::__construct($data);
    $this->updateData(["type" => "email"]);
  }

  public function onSave($value) {
    if (!filter_var($value, FILTER_VALIDATE_EMAIL))
      return new Exception("Email format is invalid");
    return $value;
  }
}
