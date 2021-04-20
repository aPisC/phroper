<?php

namespace Phapi\Model\Fields;

class FileUploadMulti extends RelationMulti {
    public function __construct($relKey) {
        parent::__construct("file-upload", $relKey);
    }

    public function getUiInfo() {
        $i = parent::getUiInfo();
        $i["type"] = "file_multi";
        return $i;
    }
}
