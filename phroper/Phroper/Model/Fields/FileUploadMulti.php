<?php

namespace Phroper\Model\Fields;

class FileUploadMulti extends RelationMulti {
    public function __construct($relKey) {
        parent::__construct("file-upload", $relKey);
        $this->updateData(["type" => "file_multi"]);
    }
}
