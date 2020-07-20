<?php
  include_once(dirname(__FILE__).'/../utils/ApiHandlers/SignedCallHandler.php');

  class TestApiHandler extends ApiCallHandler {
    function get($data)
    {
      return "get success";
    }
  }
  
  ApiCallHandler::use(new TestApiHandler())

?>