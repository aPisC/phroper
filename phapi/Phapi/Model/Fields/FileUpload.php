<?php

namespace Phapi\Model\Fields;

class FileUpload extends RelationToOne {
    public function __construct() {
        parent::__construct("file-upload");
        $this->updateData(["type" => "file"]);
    }
}
