<?php

namespace Phapi\Model\Fields;

class FileUpload extends RelationToOne {
    public function __construct($data = null) {
        parent::__construct("file-upload", ["type" => "file"]);
        $this->updateData($data);
    }
}
