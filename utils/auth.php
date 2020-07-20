<?php 
class AuthUser {
  function getApiKey(){
    return 'dummyKey';
  }
}

class AuthHandler {
  private static $_instance = null;

  public static function instance(){
    if(self::$_instance == null)
      self::$_instance = new AuthHandler();
    return self::$_instance;
  }

  function getUser($username){
    return new AuthUser();
  }
}

?>