<?php

namespace admin\Controllers;

use Phapi;
use Phapi\Controller;

class ContentManager extends Controller {
    public function __construct() {
        parent::__construct();

        // register handler functions
        $this->registerJsonHandler(':model/count', 'count', 'GET');
        $this->registerJsonHandler(':model/::id', 'findOne', 'GET');
        $this->registerJsonHandler(':model/::id', 'update', 'PUT');
        $this->registerJsonHandler(':model/::id', 'delete', 'DELETE');
        $this->registerJsonHandler(":model", 'create', 'POST');
        $this->registerJsonHandler(":model", 'find', 'GET');
    }

    public function findOne($params, $next) {
        $model = Phapi::model($params["model"]);
        return $model->findOne([$model->getPrimaryField() => $params['id']]);
    }

    public function find($params) {
        $model = Phapi::model($params["model"]);
        return $model->find([]);
    }

    public function create($params) {
        $model = Phapi::model($params["model"]);
        $data = json_load_body();
        return $model->create($data);
    }

    public function update($params, $next) {
        $model = Phapi::model($params["model"]);
        $data = json_load_body();
        return $model->update([$model->getPrimaryField() => $params['id']], $data);
    }

    public function delete($params, $next) {
        $model = Phapi::model($params["model"]);
        return $model->delete([$model->getPrimaryField() => $params['id']]);
    }

    public function count($params) {
        $model = Phapi::model($params["model"]);
        return $model->count(null);
    }
}
