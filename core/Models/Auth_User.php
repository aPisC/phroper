<?php 
namespace Models;
use Model;

class Auth_User extends Model{
  public function __construct() {    
    parent::__construct('user');

    $this->fields['username'] = array(
      'type' => 'text',
      'sqltype' => 'VARCHAR(100)',
    );
    $this->fields['role'] = array(
      'type' => 'relation',
      'model' => 'Auth_Role',
      'sqltype' => 'INT',
      'field' => 'role_id',
    );
    $this->fields['password'] = array(
      'type' => 'password',
      'sqltype' => 'VARCHAR(255)',
      'private' => true,
    );
    $this->fields['email'] = array(
      'type' => 'email',
      'sqltype' => 'VARCHAR(255)',
    );
  }

  public function allowDefaultService()
  {
    return false;
  }
}