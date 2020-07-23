<?php
namespace Models;
use Model;
class Auth_Permission extends Model{
  public function __construct() {
    parent::__construct('permission');

    $this->fields['role'] = [
      'type' => 'relation',
      'model' => 'Auth_Role',
      'sqltype' => 'INT',
    ];
    $this->fields['name'] = [
      'type' => 'text',
      'sqltype' => 'VARCHAR(100)',
    ];
  }

  public function allowDefaultService()
  {
    return true;
  }
}