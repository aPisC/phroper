<?php

namespace Controllers;

use Exception;
use Phroper\Phroper;

class FileUpload extends DefaultController {
    public function __construct() {
        parent::__construct("file-upload");
        $this->registerJsonHandler('upload', null, 'POST');
    }

    public function upload() {
        $dir = Phroper::ini("ROOT") . Phroper::ini("DS") . "uploads";
        if (!is_dir($dir))
            mkdir($dir);

        if (!isset($_FILES["file"]))
            throw new Exception("File must be provided", 500);

        $hash = md5_file($_FILES["file"]["tmp_name"]);
        $extension = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));

        $filepath = $dir . Phroper::ini("DS") . $hash . "." . $extension;

        if (!file_exists($filepath))
            move_uploaded_file($_FILES["file"]["tmp_name"], $filepath);

        $finfo = $this->service->create(["filename" => "/uploads/" . $hash . "." . $extension, "mime" => $extension]);

        return $finfo;
    }
}
