<?php

namespace Phroper\Model\Fields;

class FileUploadMulti extends RelationMulti {
    public function __construct($relKey, $data = null) {
        parent::__construct("file-upload", $relKey, $data);
        $this->updateData(["type" => "file_multi"]);
    }
}
