<?php

namespace Phroper\Model;

use Error;
use Exception;
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
                if (is_string($value))
                    $fcn = "Phroper\\Model\\Fields\\" . str_kebab_pc($value);
                else
                    $fcn = "Phroper\\Model\\Fields\\" . str_kebab_pc($value[0]);
                $arg = is_array($value)
                    ? array_filter($value, function ($i) {
                        return  $i > 0;
                    }, ARRAY_FILTER_USE_KEY)
                    : [];
                $this->fields[$key] = new $fcn(...$arg);
            }
        } catch (Exception $ex) {
            error_log($ex);
            throw $ex;
        } catch (Error $ex) {
            error_log($ex);
            throw $ex;
        }
    }

    public function isCacheable() {
        return false;
    }
}
