<?php

namespace QueryBuilder\Traits;

use Exception;
use Phapi;
use QueryBuilder\QB_Ref;

trait Filterable {
    protected bool $__trait__filterable = true;

    private string $__filterable__filter = "";


    public function filter($filter) {
        $rf = $this->__filterable__queryo2raw($filter, true);
        if ($rf) $this->addRawFilter(...$rf);
    }

    public function addRawFilter(...$filter) {
        $fsql =  $this->__filterable__raw2sql($filter);
        if (!$fsql) return;

        if ($this->__filterable__filter != "")
            $this->__filterable__filter .= " AND ";
        $this->__filterable__filter .= "(" . $fsql . ") \n";
    }


    private function __filterable__raw2sql($filter) {
        // Creates sql from raw filter array
        $operator = strtolower($filter[0]);
        $resolved = "";
        switch ($operator) {
            case "limit": // ["limit", amount]
                //if (!isset($this->limit)) throw new Exception("This query can not be limited");
                $this->limit(intval($filter[1]));
                break;
            case "offset": // ["offset", amount]
                if (!isset($this->offset)) throw new Exception("This query can not be limited");
                $this->offset(intval($filter[1]));
                break;
            case "sort": // ["sort", ...sort]
                if (!isset($this->orderBy)) throw new Exception("This query can not be ordered");
                foreach ($filter as $index => $arg) {
                    if ($index < 1) continue;
                    $this->orderBy($arg);
                }
                break;
            case "and": // ["and", ...rawFilters]
                foreach ($filter as $index => $arg) {
                    if ($index < 1) continue;
                    $fsql = is_array($arg)
                        ?  $this->__filterable__raw2sql($arg)
                        : $this->__filterable__value2sql($arg);
                    if (!$fsql) continue;
                    if ($resolved) $resolved .= " AND ";
                    $resolved .= "(" . $fsql . ")";
                }
                break;
            case "or": // ["or", ...rawFilters]
                foreach ($filter as $index => $arg) {
                    if ($index < 1) continue;
                    $fsql = is_array($arg)
                        ?  $this->__filterable__raw2sql($arg)
                        : $this->__filterable__value2sql($arg);
                    if (!$fsql) continue;
                    if ($resolved) $resolved .= " OR ";
                    $resolved .= "(" . $fsql . ")";
                }
                break;
            case "not": // ["not", ...rawFilters], combined with AND
                $r = "";
                foreach ($filter as $index => $arg) {
                    if ($index < 1) continue;
                    $fsql = is_array($arg)
                        ?  $this->__filterable__raw2sql($arg)
                        : $this->__filterable__value2sql($arg);
                    if (!$fsql) continue;
                    if ($r) $r .= " AND ";
                    $r .= "(" . $fsql . ")";
                }
                $resolved .= "NOT (" . $r . ")";
                break;
            case "in": // ["in", "target", ...list]
                $resolved .= $this->__filterable__value2sql($filter[1]) . " IN (";
                foreach ($filter as $index => $arg) {
                    if ($index < 2) continue;
                    if ($index > 2 && $index < count($filter)) $resolved .= ", ";
                    $resolved .= $this->__filterable__value2sql($arg);
                }
                $resolved .= ")";
                break;
            case "notin": // ["notin", "target", ...list]
                $resolved .= $this->__filterable__value2sql($filter[1]) . "NOT IN (";
                foreach ($filter as $index => $arg) {
                    if ($index < 2) continue;
                    if ($index > 2 && $index < count($filter)) $resolved .= ", ";
                    $resolved .= $this->__filterable__value2sql($arg);
                }
                $resolved .= ")";
                break;
            case "=": // ["=", arg1, arg2]
                $resolved .= $this->__filterable__value2sql($filter[1]) . " = " . $this->__filterable__value2sql($filter[2]);
                break;
            case "<": // ["<", arg1, arg2]
                $resolved .= $this->__filterable__value2sql($filter[1]) . " < " . $this->__filterable__value2sql($filter[2]);
                break;
            case ">": // [">", arg1, arg2]
                $resolved .= $this->__filterable__value2sql($filter[1]) . " > " . $this->__filterable__value2sql($filter[2]);
                break;
            case "<=": // ["<=", arg1, arg2]
                $resolved .= $this->__filterable__value2sql($filter[1]) . " <= " . $this->__filterable__value2sql($filter[2]);
                break;
            case ">=": // [">=", arg1, arg2]
                $resolved .= $this->__filterable__value2sql($filter[1]) . " >= " . $this->__filterable__value2sql($filter[2]);
                break;
            case "<>": // ["<>", arg1, arg2]
                $resolved .= $this->__filterable__value2sql($filter[1]) . " <> " . $this->__filterable__value2sql($filter[2]);
                break;
            case "null": // ["null", arg1]
                $resolved .= $this->__filterable__value2sql($filter[1]) . " IS NULL";
                break;
            case "notnull":  // ["notnull", arg1]
                $resolved .= $this->__filterable__value2sql($filter[1]) . " IS NOT NULL";
                break;
            case "like": // ["like", target, expression]
                $resolved .= $this->__filterable__value2sql($filter[1]) . " LIKE " . $this->__filterable__value2sql($filter[2]);
                break;
            case "notlike": // ["notlike", target, expression]
                $resolved .= $this->__filterable__value2sql($filter[1]) . " NOT LIKE " . $this->__filterable__value2sql($filter[2]);
                break;
        }

        return $resolved;
    }

    private function __filterable__value2sql($value) {
        if ($value instanceof QB_Ref) {
            $resolved = $this->resolve($value->alias);
            if (!$resolved) throw new Exception("Field '" . $value->alias . "' could not be resolved.");
            return $resolved;
        }
        return $this->bindings->push($value, "filter");
    }

    private function __filterable__querykv2raw($key, $value, $isRoot = false) {
        if ($key === "_or") {
            $args = ["or"];
            foreach ($value as $key => $part) {
                $sq = is_numeric($key)
                    ? $this->__filterable__queryo2raw($part)
                    : $this->__filterable__querykv2raw($key, $part);
                $args[] = $sq;
            }
            return $args;
        } else if ($key === "_not") {
            $args = ["not"];
            foreach ($value as $key => $part) {
                $sq = is_numeric($key)
                    ? $this->__filterable__queryo2raw($part)
                    : $this->__filterable__querykv2raw($key, $part);
                $args[] = $sq;
            }
            return $args;
        } else if ($key === "_and") {
            $args = ["and"];
            foreach ($value as $part) {
                $sq = is_numeric($key)
                    ? $this->__filterable__queryo2raw($part)
                    : $this->__filterable__querykv2raw($key, $part);
                $args[] = $sq;
            }
            return $args;
        } else if ($key === "_sort" && $isRoot) {
            $args = ["sort"];
            foreach ($value as $part) {
                $args[] = $part;
            }
            return $args;
        } else if ($key === "_limit" && $isRoot) {
            return ["limit", intval($value)];
        } else if ($key === "_start" && $isRoot) {
            return ["offset", intval($value)];
        } else if (str_ends_with($key, "_ne"))
            return ["<>", new QB_Ref(str_drop_end($key, 3)), $value];
        else if (str_ends_with($key, "_ge"))
            return [">=", new QB_Ref(str_drop_end($key, 3)), $value];
        else if (str_ends_with($key, "_le"))
            return ["<=", new QB_Ref(str_drop_end($key, 3)), $value];
        else if (str_ends_with($key, "_gt"))
            return [">", new QB_Ref(str_drop_end($key, 3)), $value];
        else if (str_ends_with($key, "_lt"))
            return ["<", new QB_Ref(str_drop_end($key, 3)), $value];
        else if (str_ends_with($key, "_in"))
            return ["in", new QB_Ref(str_drop_end($key, 3)), ...$value];
        else if (str_ends_with($key, "_notin"))
            return ["notin", new QB_Ref(str_drop_end($key, 6)), ...$value];
        else if (str_ends_with($key, "_like"))
            return ["like", new QB_Ref(str_drop_end($key, 5)), $value];
        else if (str_ends_with($key, "_notlike"))
            return ["notlike", new QB_Ref(str_drop_end($key, 5)), $value];
        else if (str_ends_with($key, "_null"))
            return [$value ? "null" : "notnull", new QB_Ref(str_drop_end($key, 5))];
        else if (str_ends_with($key, "_sort"))
            $this->addOrderByEq(str_drop_end($key, 5), $value);
        else return ["=", new QB_Ref($key), $value];
        return false;
    }

    private function __filterable__queryo2raw($query, $isRoot = false) {
        if (count($query) == 0) return false;
        $args = ["and"];
        foreach ($query as $key => $value) {
            $sq = $this->__filterable__querykv2raw($key, $value, $isRoot);
            if ($sq) $args[] = $sq;
        }

        if (count($args) == 2) return $args[1];
        if (count($args) == 1) return false;
        return $args;
    }
}
