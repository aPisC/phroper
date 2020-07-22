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
      $this->registerJsonHandler('create', function() {return $this->create();});
      $this->registerJsonHandler(':id', function($u, $p){return $this->findOne($p['id']);});
      $this->registerJsonHandler(null,function() {return  $this->find();});
    }

    public function findOne($id){
      return $this->service->findOne($id);
    }

    public function find(){
      return $this->service->find();
    }   

    public function create(){
      return $this->service->create();
    }
  }

?>