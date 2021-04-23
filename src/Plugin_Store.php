<?php

namespace Phroper;

use Phroper\Model;

class BoundStore {
    private $service;
    private $key;

    public function __construct($service, $key) {
        $this->service = $service;
        $this->key = $key;
    }

    public function get($default = null) {
        return $this->service->get($this->key, $default);
    }

    public function set($value) {
        return $this->service->get($this->key, $value);
    }
}

class Plugin_Store {
    private Model $storeModel;

    public function __construct() {
        $this->storeModel = Phroper::model("Store");
    }

    public function get($key, $default = null) {
        $entity = $this->storeModel->findOne(["key" => $key]);
        if ($entity) return $entity["value"];

        return $this->storeModel->create(["key" => $key, "value" => $default])["value"];
    }

    public function set($key, $value) {
        $entity = $this->storeModel->findOne(["key" => $key]);
        if ($entity) $this->storeModel->update(["key" => $key], ["value" => $value]);
        else $this->storeModel->create(["key" => $key, "value" => $value])["value"];
    }

    public function bind($key) {
        return new BoundStore($this, $key);
    }
}
