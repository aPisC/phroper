<?php

namespace Model\Fields;

class Password extends Field {
  public function __construct(array $data = null) {
    parent::__construct($data);
  }

  public function getSQLType() {
    return 'VARCHAR(255)';
  }

  public function onSave($value) {
    $value = parent::onSave($value);
    if ($value == null) return null;
    return password_hash($value, PASSWORD_DEFAULT);
  }

  public function isPrivate() {
    return true;
  }
}
