<?php

namespace Phroper\Controllers;

use Error;
use Exception;
use Phroper\Controller;
use Phroper\Phroper;
use Phroper\Service;

class DefaultController extends Controller {
  protected Service $service;

  public function __construct($serviceName = null) {
    parent::__construct();

    // Check service name
    if (!$serviceName)
      throw new Exception("Using defaultController without service is not allowed.");

    // Load service
    $this->service = Phroper::service($serviceName);
    if ($this->service == null || !$this->service->allowDefaultController())
      throw new Exception('Service ' . $serviceName . ' is not available.');

    // register handler functions
    $this->registerJsonHandler('/:id', 'findOne', 'GET', -1);
    $this->registerJsonHandler('/:id', 'update', 'PUT', -1);
    $this->registerJsonHandler('/:id', 'delete', 'DELETE', -1);
    $this->registerJsonHandler("/", 'create', 'POST', 0);
    $this->registerJsonHandler("/", 'find', 'GET', 0);
    $this->registerJsonHandler('/count', 'count', 'GET', 0);
  }

  public function findOne($params, $next) {
    if (!is_numeric($params['id'])) $next();
    return $this->service->findOne(["id" => $params['id']]);
  }

  public function find() {
    return $this->service->find($_GET);
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
    if (parent::getName() === "default-controller")
      return $this->service->getName();
    return parent::getName();
  }
}
