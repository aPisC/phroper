<?php

namespace Phapi\Model\Fields;

class FileUpload extends RelationToOne {
    public function __construct($data = null) {
        parent::__construct("file-upload", $data);
        $this->updateData(["type" => "file"]);
    }
}
