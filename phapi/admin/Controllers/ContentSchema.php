<?php

namespace admin\Controllers;

use Phapi;
use Phapi\Controller;

class ContentSchema extends Controller {
    public function __construct() {
        parent::__construct();

        $this->registerJsonHandler("models");
        $this->registerJsonHandler("model/:model", "model");
    }

    public function models() {
        $files = [];
        if (is_dir(ROOT . DS . "phapi" . DS . "Models"))
            $files = array_merge($files, scandir(ROOT . DS . "phapi" . DS . "Models"));
        if (is_dir(ROOT . DS . "Models"))
            $files = array_merge($files, scandir(ROOT . DS . "Models"));

        $files = array_filter($files, function ($v) {
            return !str_starts_with($v, ".") && str_ends_with($v, ".php");
        });
        $files = array_map(function ($v) {
            return str_drop_end($v, 4);
        }, $files);

        $files = array_unique(array_values($files));
        sort($files);

        return $files;
    }

    public function model($p) {
        $model = Phapi::model($p["model"]);

        $name = explode("\\", get_class($model));
        $name = $name[count($name) - 1];

        $name = str_pc_text($name);

        $result = [
            "name" => $name,
            "fields" => []
        ];

        foreach ($model->fields as $key => $field) {
            if (!$field) continue;
            $fd = $field->getUiInfo();
            if (!$fd) continue;

            $result["fields"][$key] = $fd;
        }

        return $result;
    }
}
