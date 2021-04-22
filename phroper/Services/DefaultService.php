<?php

namespace Services;

use Exception;
use Phroper;
use Phroper\Model;
use Phroper\Service;

class DefaultService extends Service {
  public Model $model;

  public function __construct($modelName = null) {

    if (!$modelName)
      throw new Exception("Using defaultService without model is not allowed.");

    $model = Phroper::model($modelName);
    if ($model == null || !$model->allowDefaultService())
      throw new Exception("Service " . $modelName . " is not available.");
    $this->model = $model;
  }

  public function getName() {
    if (parent::getName() == "services_defaultservice")
      return $this->model->getName();
    return parent::getName();
  }

  public function find($filter) {
    $entities = $this->model->find($filter);
    $model = $this->model;
    if (is_array($entities))
      return array_map(function ($entity) use ($model) {
        return $model->sanitizeEntity($entity);
      }, $entities);
    return $entities;
  }

  public function findOne($filter) {
    $entity = $this->model->findOne($filter);
    return $this->model->sanitizeEntity($entity);
  }

  public function create($entity) {
    $entity2 = $this->model->sanitizeEntity($entity);
    return $this->model->create($entity2);
  }

  public function update($filter, $entity) {
    $entity2 =  $this->model->update($filter, $entity);
    return $this->model->sanitizeEntity($entity2);
  }

  public function delete($filter) {
    $entity = $this->model->delete($filter);
    return $this->model->sanitizeEntity($entity);
  }

  public function count($filter) {
    return $this->model->count($filter);
  }
}
