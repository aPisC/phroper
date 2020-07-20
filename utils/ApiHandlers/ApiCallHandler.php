<?php
  class ApiCallHandler {
    function handle($raw){
      try{
        // Testing request authentication status
        if(!$this->isAuthCorrect($raw)){
        http_response_code(403);
        return array(
          'status' => 'ERROR',
          'message' => 'UNAUTHENTICATED'
        );
        }

        //Parse json data
        $data = json_decode($raw, true);

        // Call handler function
        if($_SERVER['REQUEST_METHOD'] ==  'GET')
          return $this->get($data);
        if($_SERVER['REQUEST_METHOD'] ==  'POST')
          return $this->post($data);
        if($_SERVER['REQUEST_METHOD'] ==  'DELETE')
          return $this->delete($data);
        if($_SERVER['REQUEST_METHOD'] ==  'PUT')
          return $this->put($data);
        throw new Exception('UNSUPPORTED');
      }
      catch(Exception $e){
        return array(
          'status' => 'ERROR',
          'message' => $e->getMessage()
        );
      }
    }

    function get($data){
      throw new Exception('UNSUPPORTED');
    }

    function post($data){
      throw new Exception('UNSUPPORTED');
    }

    function delete($data){
      throw new Exception('UNSUPPORTED');
    }

    function put($data){
      throw new Exception('UNSUPPORTED');
    }

    function isAuthCorrect($data) {
      return true;
    }

    static function use(ApiCallHandler $handler){
      $raw = file_get_contents('php://input');
      header('Content-Type: application/json');
      $ret = $handler->handle($raw);
      echo json_encode($ret);
    }
  }
?>