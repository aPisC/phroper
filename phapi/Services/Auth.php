<?php

namespace Services;

use Exception;
use Controller;
use Service;
use Model;
use JWT;

class Auth extends Service {
  private Model $userModel, $roleModel, $permModel;

  public function __construct() {
    parent::__construct();

    $this->userModel = Model::getModel("Auth_User");
    $this->roleModel = Model::getModel("Auth_Role");
    $this->permModel = Model::getModel("Auth_Permission");
  }

  public function login($username, $password) {
    $user = $this->userModel->findOne(array('username' => $username));
    if ($user == null || !password_verify($password, $user['password']))
      throw new Exception('The given credentials are incorrect', 403);
    return [
      'user' => $this->userModel->sanitizeEntity($user),
      'jwt' => JWT::generate([
        'userid' => $user['id']
      ])
    ];
  }

  public function getUser($userId) {
    return $this->userModel->sanitizeEntity(
      $this->userModel->findOne(['id' => $userId])
    );
  }

  public function register($username, $email, $password) {
    $username = trim($username);
    $email = trim($email);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new Exception("Email format is invalid");
    }
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/', $password)) {
      throw new Exception("Password is too weak.");
    }

    $entity = $this->userModel->findOne(["_or" => [
      "username" => $username,
      "email" => $email
    ]]);
    if ($entity != null)
      throw new Exception('Email or username is already in use.', 403);

    $role = $this->roleModel->findOne(["isDefault" => true], []);

    $entity = $this->userModel->create([
      'username' => $username,
      'password' => $password,
      'email' => $email,
      'role' => $role,
    ]);

    return $this->userModel->sanitizeEntity($entity);
  }

  public function havePermission($user, $permName) {
    if ($user == null)
      return $this->permModel->findOne([
        'role.isDefault' => true,
        'permission' => $permName,
      ], []) != null;
    if (is_array($user) && $user["isAdmin"] === true) return true;
    else if (
      is_scalar($user)
      && $user
      &&  $this->userModel->findOne(["id" => $user, "isAdmin" => true], []) !== null
    ) return true;

    return $this->permModel->findOne([
      'role' => is_array($user['role']) ? $user['role']['id'] : $user['role'],
      'permission' => $permName,
    ], []) != null;
  }
}
