<?php

namespace Phroper\QueryBuilder\Traits;

use Phroper\Phroper;
use Throwable;

trait Joinable {
    protected bool $__trait__joinable = true;

    private array $__joinable__joins = array();
    private string $__joinable__sql = "";
    private array $__joinable__tables = [];

    function join($join, $collFields = null) {
        // Set base table
        if (!isset($this->__joinable__tables[""]))
            $this->__joinable__tables[""] = "`" . $this->model->getTableName() . "`";

        // Create join if not exist
        if (!isset($this->__joinable__joins[$join])) {
            // Resolve base field of join
            $resolved_join = $this->resolve($join);
            if (!$resolved_join || !$resolved_join["source"]) return;

            // Test if the field can be joined
            if (!($this->fields[$join]["field"] && $this->fields[$join]["field"]->isJoinable()))
                return;

            // Get model for join and store it
            $model = Phroper::model($this->fields[$join]["field"]->getModel());
            $this->__joinable__joins[$join] = $model;
            $this->__joinable__tables[$join] = "`" . $model->getTableName() . "_" . count($this->__joinable__tables) . "`";

            // Add sql for join
            if ($this->__joinable__sql) $this->__joinable__sql .= "\n";
            $this->__joinable__sql .= "LEFT OUTER JOIN `" . $model->getTableName() . "` as " . $this->__joinable__tables[$join] . " ";

            // Filter join
            if ($this->fields[$join]["field"]->useDefaultJoin())
                $this->__joinable__sql .= "ON " . $this->__joinable__tables[$join] . ".`id` = " . $this->fields[$join]["source"] . "";
            else $this->__joinable__sql .= "ON TRUE";
        }

        // Register fields from join
        $model = $this->__joinable__joins[$join];

        if ($collFields == null) {
            $collFields = $model->fields->keys();
        }

        foreach ($collFields as $key) {
            $field = isset($model->fields[$key])
                ? $model->fields[$key]
                : null;

            if (!$field) continue;

            $fieldName = $field->getFieldName($key);
            $alias = $join . "." . $key;
            $source = $this->__joinable__tables[$join] . ".`" . $fieldName . "`";

            $this->fields[$alias] =  array(
                "source" => $field->isVirtual() ? false : $source,
                "alias" => $alias,
                "field" => $field,
                "hidden" => $field->isVirtual(),
                "in_relation" => true,
            );
        }
    }

    function populate($populate) {
        foreach ($populate as $p) {
            try {
                $this->join($p);
            } catch (Throwable $t) {
            }
        }
    }
}
