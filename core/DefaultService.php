<?php
class DefaultService extends Service
{
  public Model $model;

  public function __construct($modelName) {
    $model = Model::getModel($modelName);
    if($model == null || !$model->allowDefaultService())
      throw new Exception("Auto model usage is not available for " . $modelName);
    $this->model = $model;
  }

  public function find($filter = null) {
    return $this->model->find($filter);
  }

  public function findOne($filter = null) {
    return $this->model->findOne($filter);
  }

  public function create($entity = null) {
    return $this->model->create($entity);
  }
}
