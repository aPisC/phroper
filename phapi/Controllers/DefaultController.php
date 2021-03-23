<?php

namespace Controllers;

use Exception;
use Phapi;
use Phapi\Controller;
use Phapi\Service;

class DefaultController extends Controller {
  protected Service $service;

  public function __construct($serviceName = null) {
    parent::__construct();

    // Check service name
    if (!$serviceName)
      throw new Exception("Using defaultController without service is not allowed.");

    // Load service
    $this->service = Phapi::service($serviceName);
    if ($this->service == null || !$this->service->allowDefaultController())
      throw new Exception('Service ' . $serviceName . ' is not available.');

    // register handler functions
    $this->registerJsonHandler(':id', 'findOne', 'GET');
    $this->registerJsonHandler(':id', 'update', 'PUT');
    $this->registerJsonHandler(':id', 'delete', 'DELETE');
    $this->registerJsonHandler(null, 'create', 'POST');
    $this->registerJsonHandler(null, 'find', 'GET');
    $this->registerJsonHandler('count', 'count', 'GET');
  }

  public function findOne($params, $next) {
    if (!is_numeric($params['id'])) $next();
    return $this->service->findOne(["id" => $params['id']]);
  }

  public function find() {
    $data = json_load_body();
    return $this->service->find($data);
  }

  public function create() {
    $data = json_load_body();
    return $this->service->create($data);
  }

  public function update($params, $next) {
    if (!is_numeric($params['id'])) $next();
    $data = json_load_body();
    return $this->service->update(["id" => $params['id']], $data);
  }

  public function delete($params, $next) {
    if (!is_numeric($params['id'])) $next();
    return $this->service->delete(["id" => $params['id']]);
  }

  public function count() {
    return $this->service->count(null);
  }

  public function getName() {
    if (parent::getName() === "defaultcontroller")
      return $this->service->getName();
    return parent::getName();
  }
}
