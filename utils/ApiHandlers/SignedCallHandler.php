<?php
  include_once(dirname(__FILE__).'/ApiCallHandler.php');
  include_once(dirname(__FILE__).'/../auth.php');
  

  class SignedCallHandler extends ApiCallHandler{
    protected ?AuthUser $user = null;

    function isAuthCorrect($raw)
    {
      $auth = AuthHandler::instance();

      if(!isset($_GET['user'])) return false;
      if(!isset($_GET['sign'])) return false;
      $this->user = $auth->getUser($_GET['user']);
      if($this->user == null) return false;

      return hash_hmac('sha384', $raw, $this->user->getApiKey()) == $_GET['sign'];
    }
  }
?>