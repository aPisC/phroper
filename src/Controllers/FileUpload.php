<?php

namespace Phroper\Controllers;

use Exception;
use Phroper\Phroper;

class FileUpload extends DefaultController {
    public function __construct() {
        parent::__construct("file-upload");
        $this->registerJsonHandler('/upload', null, 'POST');
    }

    public function upload() {
        $dir = Phroper::ini("ROOT") . DIRECTORY_SEPARATOR . "uploads";
        if (!is_dir($dir))
            mkdir($dir);

        if (!isset($_FILES["file"]))
            throw new Exception("File must be provided", 500);

        $hash = md5_file($_FILES["file"]["tmp_name"]);
        $extension = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));

        $filepath = $dir . DIRECTORY_SEPARATOR . $hash . "." . $extension;

        if (!file_exists($filepath))
            move_uploaded_file($_FILES["file"]["tmp_name"], $filepath);

        $finfo = $this->service->create(["filename" => "/uploads/" . $hash . "." . $extension, "mime" => $extension]);

        return $finfo;
    }
}
