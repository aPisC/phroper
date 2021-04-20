<?php


namespace Phapi\Model\Fields;

use Phapi;

class UpdatedBy extends RelationToOne {
  public function __construct() {
    parent::__construct("AuthUser", [
      "field" => "updated_by",
      "forced" => true,
      "readonly" => true,
      "auto" => true,
    ]);
  }

  public function onSave($value) {
    $user = Phapi::context('user');
    if ($user) return $user["id"];
    return null;
  }
}
