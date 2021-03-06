<?php

namespace Model\Fields;

class UpdatedAt extends Timestamp {
  public function __construct() {
    parent::__construct(["field" => "updated_at"]);
  }

  public function forceUpdate() {
    return true;
  }

  public function onSave($value) {
    return parent::onSave(time());
  }
}