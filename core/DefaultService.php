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

  public function find($filter) {
    return $this->model->find($filter);
  }

  public function findOne($filter) {
    return $this->model->findOne($filter);
  }

  public function create($entity) {
    return $this->model->create($entity);
  }

  public function update($filter, $entity) {
    return $this->model->update($filter, $entity);
  }

  public function delete($filter) {
    return $this->model->delete($filter);
  }

  public function count($filter) {
    return $this->model->count($filter);
  }
}
