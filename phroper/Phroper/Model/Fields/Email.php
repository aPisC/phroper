<?php

namespace Phroper\Model\Fields;

use Exception;

class Email extends Text {
  public function __construct($data = null) {
    parent::__construct(["type" => "email"]);
    $this->updateData($data);
  }

  public function onSave($value) {
    if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL))
      throw $this->validationError("email", "Email format is invalid");

    return parent::onSave($value);
  }
}
