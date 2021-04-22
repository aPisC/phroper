<?php

namespace admin\Controllers;

use Exception;
use Phroper;
use Phroper\Controller;

class ContentSchema extends Controller {
    public function __construct() {
        parent::__construct();

        $this->registerJsonHandler("models");
        $this->registerJsonHandler("model/:model", "model");
    }

    public function models() {
        $files = [];
        if (is_dir(ROOT . DS . "phroper" . DS . "Models"))
            $files = array_merge($files, scandir(ROOT . DS . "phroper" . DS . "Models"));
        if (is_dir(ROOT . DS . "Models"))
            $files = array_merge($files, scandir(ROOT . DS . "Models"));

        $files = array_filter($files, function ($v) {
            return !str_starts_with($v, ".") && str_ends_with($v, ".php");
        });
        $files = array_map(function ($v) {
            try {
                return Phroper::model(str_drop_end($v, 4))->getUiInfo();
            } catch (Exception $e) {
                return null;
            }
        }, array_unique($files));
        $files = array_filter($files, function ($v) {
            return !!$v;
        });

        $files = array_values($files);
        sort($files);

        return $files;
    }

    public function model($p) {
        $model = Phroper::model($p["model"]);
        return $model->getUiInfo();
    }
}
