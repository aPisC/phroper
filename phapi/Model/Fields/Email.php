<?php

namespace Model\Fields;

use Exception;

class Email extends Text {
  public function __construct($data) {
    parent::__construct($data);
  }

  public function onSave($value) {
    if (!filter_var($value, FILTER_VALIDATE_EMAIL))
      return new Exception("Email format is invalid");
    return $value;
  }
}
