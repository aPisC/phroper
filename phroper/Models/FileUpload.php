<?php

namespace Models;

use Phroper\Model;
use Phroper\Model\Fields\Text;


class FileUpload extends Model {
    public function __construct() {
        parent::__construct();

        $this->fields["updated_at"] = null;
        $this->fields["filename"] = new Text();
        $this->fields["mime"] = new Text();
    }
}
