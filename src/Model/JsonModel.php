<?php

namespace Phroper\Model;

use Error;
use Exception;
use Phroper\Fields\Field;
use Phroper\Model;

class JsonModel extends Model {
    public function __construct($filename) {
        try {
            // Load file
            $data = json_decode(file_get_contents($filename), true);

            // Create model data
            $name = basename($filename, ".json");
            $key = str_pc_kebab($name);
            $name = str_pc_text($name);
            parent::__construct([
                "sql_table" => $key,
                "key" => $key,
                "name" => $name,
            ]);
            $this->updateData($data);

            // Create fields
            foreach ($data["fields"] as $key => $value) {
                if (!$value) {
                    $this->fields[$key] = null;
                    continue;
                }

                $this->fields[$key] = Field::createField($value);
            }
        } catch (Exception $ex) {
            error_log($ex);
            throw $ex;
        } catch (Error $ex) {
            error_log($ex);
            throw $ex;
        }
    }
}
