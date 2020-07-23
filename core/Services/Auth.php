<?php 
namespace Services;

use Exception;
use Service;
use Model;
use JWT;

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
      throw new Exception('The given credentials are incorrect', 403);
    return [
      'user' => $this->userModel->sanitizeEntity($user),
      'jwt' => JWT::generate([
        'userid' => $user['id']
      ])
    ];
  }

  public function getUser($userId){
    return $this->userModel->sanitizeEntity(
      $this->userModel->findOne(['id' => $userId])
    );
  }
}

?>