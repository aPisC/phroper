<?php

  class DefaultController extends Controller {
    protected Service $service;
    protected string $serviceName;

    public function __construct($serviceName) {
      parent::__construct();

      // Initilalize model
      $this->serviceName = $serviceName;
      $service = Service::getService($serviceName);
      if($service == null || !$service->allowDefaultController())
        throw new Exception('Service ' . $serviceName . ' could not be loaded automatically.' );
      $this->service = $service;

      // register handler functions
      $this->registerJsonHandler('count', function($u, $p){return $this->count($p);}, 'GET');
      $this->registerJsonHandler(':id', function($u, $p){return $this->findOne($p);}, 'GET');
      $this->registerJsonHandler(':id', function($u, $p){return $this->update($p);}, 'PUT');
      $this->registerJsonHandler(':id', function($u, $p){return $this->delete($p);}, 'DELETE');
      $this->registerJsonHandler(null, function($u, $p) {return $this->create();}, 'POST');
      $this->registerJsonHandler(null,function() {return  $this->find();}, 'GET');
    }

    public function findOne($params){
      return $this->service->findOne($params['id']);
    }

    public function find(){
      $data = json_load_body();
      return $this->service->find($data);
    }   

    public function create(){
      $data = json_load_body();
      return $this->service->create($data);
    }

    public function update($params){
      $data = json_load_body();
      return $this->service->update($params['id'], $data);
    }

    public function delete($params){
      return $this->service->delete($params['id']);
    }

    public function count($params){
      return $this->service->count(null);
    }
  }

?>