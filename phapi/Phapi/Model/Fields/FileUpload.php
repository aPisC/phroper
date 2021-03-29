<?php

namespace Phapi\Model\Fields;

class FileUpload extends RelationToOne {
    public function __construct() {
        parent::__construct("file-upload");
    }

    public function getUiInfo() {
        $i = parent::getUiInfo();
        $i["type"] = "file";
        return $i;
    }
}
