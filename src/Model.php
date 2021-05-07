<?php

namespace Phroper;

use Exception;
use Phroper\Phroper;
use Phroper\Model\Entity;
use Phroper\Model\EntityList;
use Phroper\Model\FieldCollection;
use Phroper\Fields\IgnoreField;
use Phroper\Fields\Relation;
use Phroper\QueryBuilder\Query\Count;
use Phroper\QueryBuilder\Query\CreateTable;
use Phroper\QueryBuilder\Query\Delete;
use Phroper\QueryBuilder\Query\Insert;
use Phroper\QueryBuilder\Query\Select;
use Phroper\QueryBuilder\Query\Update;

class Model {
  protected array $data = [];
  public FieldCollection $fields;

  public function __construct($data = null) {
    Phroper::model_cache_callback($this);

    $name = explode("\\", get_class($this));
    $name = end($name);
    $key = str_pc_kebab($name);
    $name = str_pc_text($name);

    $this->updateData([
      "default_service" => true,
      "sql_table" => $key,
      "key" => $key,
      "name" => $name,
      "primary" => "id",
      "display" => "id",
      "visible" => true,
      "editable" => true,
    ]);
    $this->updateData($data);

    $this->fields = new FieldCollection($this);
    $this->fields["id"] = new Fields\Identity();
    $this->fields["updated_by"] = new Fields\UpdatedBy();
    $this->fields["created_at"] = new Fields\CreatedAt();
    $this->fields["updated_at"] = new Fields\UpdatedAt();
  }

  public function getUiInfo(): array {
    $data = [];

    foreach ($this->data as $key => $value) {
      if (!is_scalar($value) && !is_array(($value))) continue;
      if (str_starts_with($key, "sql_")) continue;
      $data[$key] = $value;
    }
    $data["fields"] = [];
    foreach ($this->fields as $key => $field) {
      if (!$field) continue;
      $fd = $field->getUiInfo();
      if (!$fd) continue;

      $data["fields"][$key] = $fd;
    }

    return $data;
  }


  protected function updateData($data): void {
    if (!$data) return;

    foreach ($data as $key => $value) {
      $this->data[$key] = $value;
    }
  }

  public function allowDefaultService(): bool {
    return $this->data["default_service"];
  }

  public function getName(): string {
    return $this->data["key"];
  }

  public function getPrimaryField(): string | array {
    return $this->data["primary"];
  }

  function getTableName(): string {
    return $this->data["sql_table"];
  }

  function getDisplayField(): string {
    return $this->data["display"];
  }

  function getPopulateList($populate = null): ?array {
    if (is_array($populate)) return $populate;

    if (isset($this->data["populate"])) return $this->data["populate"];

    $populate = [];
    foreach ($this->fields as $key => $field) {
      if (!$field) continue;
      if ($field->isDefaultPopulated())
        $populate[] = $key;
    }
    return $populate;
  }

  public function sanitizeEntity($entity): ?array {
    if ($entity instanceof Model\LazyResult)
      $entity = $entity->get();
    if ($entity === null) return null;
    if (is_scalar($entity)) return $entity;
    $ne = array();
    foreach ($this->fields as $key => $field) {
      if (!$field) continue;
      if (is_array($entity) && !array_key_exists($key, $entity)) continue;
      if ($entity instanceof Entity && !$entity->offsetExists($key)) continue;
      $sv = $entity[$key];
      if ($sv instanceof Model\LazyResult)
        $sv = $sv->get();
      $sv = $field->getSanitizedValue($sv);
      if (!($sv instanceof Fields\IgnoreField))
        $ne[$key] = $sv;
    }
    return $ne;
  }

  public function restoreEntity($assoc, $populate, $prefix = ""): Entity {
    $entity = new Entity($this);

    // First of all, restore id, because it may be required for relation loading
    $memberName = $prefix == "" ? "id" : ($prefix . ".id");
    if (isset($assoc[$memberName]))
      $entity["id"] = $assoc[$memberName];
    else if (isset($assoc[$prefix])) return $assoc[$prefix];
    else if (isset($this->fields["id"])) return null;

    // Restore other fields
    foreach ($this->fields as $key => $field) {
      if (!$field) continue;

      $v = null;
      $memberName = $prefix == "" ? $key : ($prefix . "." . $key);

      if (array_key_exists($memberName, $assoc))
        $v = $field->onLoad($assoc[$memberName], $memberName, $assoc, $populate);
      else if ($field->isVirtual()) {
        $v = $field->onLoad($entity["id"], $memberName, $assoc, $populate);
      }
      if (!($v instanceof Fields\IgnoreField)) $entity[$key] = $v;
    }
    return $entity;
  }


  //--------------------------
  // Data managing functions
  //--------------------------

  protected function postUpdate($entity, $updated): bool {
    $hadPostUpdate = false;
    foreach ($this->fields as $key => $field) {
      if (!$field) continue;
      if (array_key_exists($key, $entity))
        $hadPostUpdate = $hadPostUpdate || $field->postUpdate($entity[$key], $key, $updated);
      else if ($field->forceUpdate())
        $hadPostUpdate = $hadPostUpdate || $field->postUpdate(null, $key, $updated);
    }
    return $hadPostUpdate;
  }

  protected function postInsert($entity, $updated): bool {
    $hadPostUpdate = false;
    foreach ($this->fields as $key => $field) {
      if (!$field) continue;
      if (array_key_exists($key, $entity))
        $hadPostUpdate = $hadPostUpdate || $field->postUpdate($entity[$key], $key, $updated);
      else if (!($field->getDefault() instanceof IgnoreField))
        $hadPostUpdate = $hadPostUpdate || $field->postUpdate($field->getDefault(), $key, $updated);
      else if ($field->forceUpdate() || $field->isRequired())
        $hadPostUpdate = $hadPostUpdate || $field->postUpdate(null, $key, $updated);
    }
    return $hadPostUpdate;
  }

  private function useFilter($q, $filter): void {
    if (is_array($filter) && count($filter) > 0) {
      $q->filter($filter);
    } else if (is_string($filter) || is_numeric($filter)) {
      $q->addRawFilter("=", new QueryBuilder\QB_Ref('id'), $filter);
    }
  }

  public function findOne($filter, $populate = null): ?Entity {
    $populate = $this->getPopulateList($populate);
    $mysqli = Phroper::instance()->getMysqli();

    $q = new Select($this);
    $q->populate($populate);
    $this->useFilter($q, $filter);
    $q->limit(1);

    $result = $q->execute($mysqli);

    if (!$result) {
      throw new Exception('Database error ' . $mysqli->error);
    }
    if ($result->num_rows == 0) {
      $result->free_result();
      return null;
    }

    $assoc = $result->fetch_assoc();
    $result->free_result();

    return $this->restoreEntity($assoc, $populate);
  }

  public function find($filter, $populate = null): EntityList {
    $populate = $this->getPopulateList($populate);
    $mysqli = Phroper::instance()->getMysqli();

    $q = new Select($this);
    $q->populate($populate);
    $this->useFilter($q, $filter);

    $result = $q->execute($mysqli);

    if (!$result) {
      throw new Exception('Database error ' . $mysqli->error);
    }

    if ($result->num_rows == 0) {
      $result->free_result();
      return new EntityList($this);
    }

    $assocs = $result->fetch_all(MYSQLI_ASSOC);
    $result->free_result();
    return new EntityList($this, array_map(fn ($assoc) => $this->restoreEntity($assoc, $populate), $assocs));
  }

  public function count($filter): ?int {
    $mysqli = Phroper::instance()->getMysqli();

    $q = new Count($this);
    $this->useFilter($q, $filter);
    $result = $q->execute($mysqli);

    if (!$result) {
      throw new Exception('Database error ' . $mysqli->error);
    }

    if ($result->num_rows == 0) {
      $result->free_result();
      return null;
    }

    $entity = $result->fetch_array();
    $result->free_result();
    return $entity[0];
  }

  public function create($entity): ?Entity {
    return $this->createMulti([$entity])[0];
  }

  public function createMulti($entities, $processEntities = true): ?EntityList {
    if (count($entities) == 0) return [];

    $mysqli = Phroper::instance()->getMysqli();

    $q = new Insert($this);

    return $q->withTransaction($mysqli, function () use ($q, $entities, $processEntities, $mysqli) {
      foreach ($entities as $index => $entity) {
        if ($index > 0) $q->nextEntity();
        $q->setAllValue($entity);
      }

      $result = $q->execute($mysqli);

      if (!$result) {
        throw new Exception('Database error ' . $mysqli->error);
      }

      if (!$processEntities) return;

      $updated = $entities;
      $insid = $mysqli->insert_id;
      if (isset($this->fields["id"])) {
        $updated = $this->find([
          "_limit" => count($entities),
          "id_ge" => $insid
        ]);
      }

      $hadPostUpdate = false;
      foreach ($entities as $i => $entity) {
        $hadPostUpdate = $hadPostUpdate || $this->postInsert($entity, $updated[$i]);
      }

      if ($hadPostUpdate && isset($this->fields["id"])) {
        $updated = $this->find([
          "_limit" => count($entities),
          "id_ge" => $insid
        ]);
      }

      return $updated;
    });
  }

  public function update($filter, $entity): Entity|EntityList|null {
    $mysqli = Phroper::instance()->getMysqli();

    $q = new Update($this, "update");

    return $q->withTransaction($mysqli, function () use ($q, $filter, $entity, $mysqli) {
      $this->useFilter($q, $filter);
      $q->setAllValue($entity);

      $result = $q->execute($mysqli);

      if (!$result) {
        throw new Exception('Database error ' . $mysqli->error);
      }

      $updated = $this->find($filter);

      $hadPostUpdate = false;
      foreach ($updated as $u) {
        $hadPostUpdate = $hadPostUpdate || $this->postUpdate($entity, $u);
      }

      if ($hadPostUpdate) $updated = $this->find($filter);

      if ($updated->count() == 0) return null;
      if ($updated->count() == 1) return $updated[0];

      return $updated;
    });
  }

  public function delete($filter, $returnEntities = true): Entity|EntityList|null {
    $entity = $returnEntities ?  $this->find($filter)->sanitizeEntity() : null;

    if ($entity || !$returnEntities) {
      $q = new Delete($this);
      $mysqli = Phroper::instance()->getMysqli();

      $this->useFilter($q, $filter);


      $result = $q->execute($mysqli);

      if (!$result) {
        throw new Exception('Database error ' . $mysqli->error);
      }
    }
    if (!$returnEntities || count($entity) == 0) return null;
    if (count($entity) == 1) return $entity[0];
    return $entity;
  }


  private bool $__initializing = false;
  public function init(): bool {
    try {
      $this->findOne([]);
    } catch (Exception $ex) {
      if ($this->__initializing)
        return false;
      $this->__initializing = true;
      // init real relations
      foreach ($this->fields as $field) {
        if ($field instanceof Relation && !$field->isVirtual()) {
          //var_dump($field);
          $field->getModel()->init();
        }
      }

      // init self
      $q = new CreateTable($this);
      $mysqli = Phroper::instance()->getMysqli();

      $q->execute($mysqli);

      // init virtual relations
      foreach ($this->fields as $field) {
        if ($field instanceof Relation && $field->isVirtual())
          $field->getModel()->init();
      }
      return true;
    }
    return false;
  }
}
