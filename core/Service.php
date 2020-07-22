<?php 
class Service{

  public function allowDefaultController(){
    return true;
  }

  public static function getService($serviceName){
    $scn= 'Services\\'. ucfirst($serviceName);
    if(class_exists($scn)) $service = new $scn();
    else $service = new DefaultService($serviceName);
    return $service;
  }


  public function __construct() {
  }

  function find($filter = null){
    throw new Exception('Find is not implemented in service');
  }  
  function findOne($filter = null){
    throw new Exception('FindOne is not implemented in service');
  }
  function create($entity = null){
    throw new Exception('FindOne is not implemented in service');
  }
}
?>