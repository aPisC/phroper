<?php

namespace admin\Controllers;

use Exception;
use Phroper;
use Phroper\Controller;

class ContentManager extends Controller {
    public function __construct() {
        parent::__construct();

        // register handler functions
        $this->registerJsonHandler(":model", 'find', 'GET');
        $this->registerJsonHandler(':model/::id', 'findOne', 'GET', -1);
        $this->registerJsonHandler(":model", 'create', 'POST');
        $this->registerJsonHandler(':model/::id', 'update', 'PUT', -1);
        $this->registerJsonHandler(':model/::id', 'delete', 'DELETE', -1);
        $this->registerJsonHandler(':model/count', 'count', 'GET');
    }

    public function findOne($params, $next) {
        $model = Phroper::model($params["model"]);
        $uiInfo = $model->getUiInfo();
        if (!$uiInfo)
            throw new Exception("Using this model is not allowed", 403);
        return $model->findOne([$model->getPrimaryField() => $params['id']]);
    }

    public function find($params) {
        $model = Phroper::model($params["model"]);
        $uiInfo = $model->getUiInfo();
        if (!$uiInfo)
            throw new Exception("Using this model is not allowed", 403);
        return $model->find($_GET);
    }

    public function create($params) {
        $model = Phroper::model($params["model"]);
        $uiInfo = $model->getUiInfo();
        if (!$uiInfo || !$uiInfo["editable"])
            throw new Exception("Using this model is not allowed", 403);
        $data = json_load_body();
        return $model->create($data);
    }

    public function update($params, $next) {
        $model = Phroper::model($params["model"]);
        $uiInfo = $model->getUiInfo();
        if (!$uiInfo || !$uiInfo["editable"])
            throw new Exception("Using this model is not allowed", 403);
        $data = json_load_body();
        return $model->update([$model->getPrimaryField() => $params['id']], $data);
    }

    public function delete($params, $next) {
        $model = Phroper::model($params["model"]);
        $uiInfo = $model->getUiInfo();
        if (!$uiInfo || !$uiInfo["editable"])
            throw new Exception("Using this model is not allowed", 403);
        return $model->delete([$model->getPrimaryField() => $params['id']]);
    }

    public function count($params) {
        $model = Phroper::model($params["model"]);
        $uiInfo = $model->getUiInfo();
        if (!$uiInfo)
            throw new Exception("Using this model is not allowed", 403);
        return $model->count(null);
    }
}
