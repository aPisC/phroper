<?php


namespace Phroper\Model\Fields;

use Phroper;

class UpdatedBy extends RelationToOne {
  public function __construct() {
    parent::__construct("AuthUser", [
      "field" => "updated_by",
      "forced" => true,
      "readonly" => true,
      "auto" => true,
      "delete_action" => null,
    ]);
  }

  public function getSQLConstraint() {
    return null;
  }

  public function onSave($value) {
    $user = Phroper::context('user');
    if ($user) return $user["id"];
    return null;
  }
}
