<?php

namespace Controllers;

use Exception;

class FileUpload extends DefaultController {
    public function __construct() {
        parent::__construct("file-upload");
        $this->registerJsonHandler('upload', null, 'POST');
    }

    public function upload() {
        $dir = ROOT . DS . "uploads";
        if (!is_dir($dir))
            mkdir($dir);

        if (!isset($_FILES["file"]))
            throw new Exception("File must be provided", 500);

        $hash = md5_file($_FILES["file"]["tmp_name"]);
        $extension = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));

        $filepath = $dir . DS . $hash . "." . $extension;

        if (!file_exists($filepath))
            move_uploaded_file($_FILES["file"]["tmp_name"], $filepath);

        $finfo = $this->service->create(["filename" => "/uploads/" . $hash . "." . $extension, "mime" => $extension]);

        return $finfo;
    }
}
