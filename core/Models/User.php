<?php 
namespace Models;
use Model;

class User extends Model{
  public function __construct() {    
    parent::__construct('user');

    $this->fields['username'] = array(
      'type' => 'text',
      'sqltype' => 'VARCHAR(100)',
    );
    $this->fields['role'] = array(
      'type' => 'relation',
      'model' => 'Role',
      'sqltype' => 'INT',
      'field' => 'role_id',
    );
    $this->fields['password'] = array(
      'type' => 'password',
      'sqltype' => 'VARCHAR(255)',
      'private' => true,
    );
  }

  public function allowDefaultService()
  {
    return true;
  }
}