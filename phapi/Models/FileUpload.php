<?php

namespace Models;

use Phapi\Model;
use Phapi\Model\Fields\Text;


class FileUpload extends Model {
    public function __construct() {
        parent::__construct();

        $this->fields["updated_at"] = null;
        $this->fields["filename"] = new Text();
        $this->fields["mime"] = new Text();
    }
}
