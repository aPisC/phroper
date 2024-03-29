<?php

namespace Phroper\Models;

use Phroper\Model;
use Phroper\Fields\Text;

class FileUpload extends Model {
    public function __construct() {
        parent::__construct([
            "visible" => false, 
            "sql_table" => "phroper_file"
        ]);

        $this->fields["updated_at"] = null;
        $this->fields["filename"] = new Text();
        $this->fields["mime"] = new Text();
    }
}
