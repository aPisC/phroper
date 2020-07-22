<?php 
namespace Services;

use Exception;
use Service;
use Model;

class Auth extends Service{
  private Model $userModel, $roleModel;

  public function __construct() {
    parent::__construct();

    $this->userModel = Model::getModel("User");
    $this->roleModel = Model::getModel("Role");
  }

  public function login($username, $password){
    $user = $this->userModel->findOne(array('username' => $username));
    if($user == null || !password_verify($password, $user['password']))
      throw new Exception('The given credentials are incorrect');
    return $this->userModel->sanitizeEntity($user);
  }
}

?>