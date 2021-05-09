<?php

use Phroper\Fields\Field;
use Phroper\Fields\IgnoreField;
use Phroper\Model;
use Phroper\Model\Entity;
use Phroper\Model\EntityList;
use Phroper\QueryBuilder\Query\CreateTable;
use Phroper\QueryBuilder\Query\Select;

class Query_Select_Exception extends Select {
    public function execute($mysqli) {
        throw new Exception("Statement could not be prepared");
    }
}

class Query_CreateTable_Dummy extends CreateTable {
    public function execute($mysqli) {
        self::$execStack[] = $this->getQuery();
    }
}



trait AutoFieldTest {
    public function testFieldParameters() {

        $f = new ($this->autoFieldTest__fieldType)(["auto"]);
        $this->assertEquals(true, $f->isAuto());

        $f = new ($this->autoFieldTest__fieldType)(["default" => 3]);
        $this->assertEquals(3, $f->getDefault());

        $f = new ($this->autoFieldTest__fieldType)(["forced"]);
        $this->assertEquals(true, $f->forceUpdate());

        $f = new ($this->autoFieldTest__fieldType)(["private"]);
        $this->assertEquals(true, $f->isPrivate());

        $f = new ($this->autoFieldTest__fieldType)(["populate"]);
        $this->assertEquals(true, $f->isDefaultPopulated());

        $f = new ($this->autoFieldTest__fieldType)(["readonly"]);
        $this->assertEquals(true, $f->isReadonly());

        $f = new ($this->autoFieldTest__fieldType)(["required"]);
        $this->assertEquals(true, $f->isRequired());

        $f = new ($this->autoFieldTest__fieldType)(["virtual"]);
        $this->assertEquals(true, $f->isVirtual());
    }

    public function testUiInfo() {
        $model = new Model();
        $model->fields["field"] = new ($this->autoFieldTest__fieldType)();
        $this->assertMatchesJsonSnapshot($model->fields["field"]->getUiInfo());

        $model = new Model();
        $model->fields["field"] = new ($this->autoFieldTest__fieldType)([
            "auto",
            "default" => "default",
            "forced",
            "private",
            "populate",
            "readonly",
            "required",
            "virtual",
            "unique",
            "type" => "number",
            "custom_info" => "custom_value",
            "sql__custom" => 3,
        ]);
        $this->assertMatchesJsonSnapshot($model->fields["field"]->getUiInfo());
    }

    public function testPrivateFieldSanitizing() {
        $f = new ($this->autoFieldTest__fieldType)(["private"]);
        $this->assertEquals(IgnoreField::instance(), $f->getSanitizedValue(null));
        $this->assertEquals(IgnoreField::instance(), $f->getSanitizedValue(10));
        $this->assertEquals(IgnoreField::instance(), $f->getSanitizedValue("string"));
        $this->assertEquals(IgnoreField::instance(), $f->getSanitizedValue(["key" => "value"]));
        $this->assertEquals(IgnoreField::instance(), $f->getSanitizedValue([1, 2, 3]));
        $this->assertEquals(IgnoreField::instance(), $f->getSanitizedValue(new Entity(null, [])));
        $this->assertEquals(IgnoreField::instance(), $f->getSanitizedValue(new EntityList(null, [])));
    }

    public function testRequiredSaving() {
        try {
            $f = new ($this->autoFieldTest__fieldType)([
                "required",
                "name" => "My Field"
            ]);
            $f->onSave(null);
            $this->fail("Exception not thrown");
        } catch (Throwable $e) {
        }


        try {
            $f = new ($this->autoFieldTest__fieldType)([
                "required",
                "msg_error_required" => "custom error message",
                "name" => "My Field"
            ]);
            $f->onSave(null);
            $this->fail("Exception not thrown");
        } catch (Throwable $e) {
            $this->assertEquals("custom error message", $e->getMessage());
        }
    }

    public function testConstraint() {
        $model = new Model();
        $model->fields["field"] = new ($this->autoFieldTest__fieldType)();
        $this->assertMatchesSnapshot($model->fields["field"]->getSQLConstraint());

        $model = new Model();
        $model->fields["field"] = new ($this->autoFieldTest__fieldType)([
            "auto",
            "default" => "default",
            "forced",
            "private",
            "populate",
            "readonly",
            "required",
            "virtual",
            "unique",
            "type" => "number",
            "custom_info" => "custom_value",
            "sql__custom" => 3,
            "sql_field" => "custom_field",
            "sql_type" => "VARCHAR",
            "sql_length" => 200,
        ]);
        $this->assertMatchesSnapshot($model->fields["field"]->getSQLConstraint());
    }

    public function testSqlType() {
        $model = new Model();
        $model->fields["field"] = new ($this->autoFieldTest__fieldType)();
        $this->assertMatchesSnapshot($model->fields["field"]->getSQLType());

        $model = new Model();
        $model->fields["field"] = new ($this->autoFieldTest__fieldType)([
            "auto",
            "default" => "default",
            "forced",
            "private",
            "populate",
            "readonly",
            "required",
            "virtual",
            "unique",
            "type" => "number",
            "custom_info" => "custom_value",
            "sql__custom" => 3,
            "sql_field" => "custom_field",
            "sql_type" => "VARCHAR",
            "sql_length" => 200,
        ]);
        $this->assertMatchesSnapshot($model->fields["field"]->getSQLType());
    }

    public function testTableCreation() {
        $model = new Model();
        $model->fields->clear();
        $model->fields["field"] = new ($this->autoFieldTest__fieldType)();
        $this->assertMatchesSnapshot((new CreateTable($model))->getQuery());

        $model = new Model();
        $model->fields->clear();
        $model->fields["field"] = new ($this->autoFieldTest__fieldType)([
            "auto",
            "default" => "default",
            "forced",
            "private",
            "populate",
            "readonly",
            "required",
            "unique",
            "type" => "number",
            "custom_info" => "custom_value",
            "sql__custom" => 3,
            "sql_field" => "custom_field",
            "sql_type" => "VARCHAR",
            "sql_length" => 200,
        ]);
        $this->assertMatchesSnapshot((new CreateTable($model))->getQuery());
    }

    public function testFieldIsType() {
        $f =  new ($this->autoFieldTest__fieldType)();
        $this->assertTrue($f->is(Field::class));
        $this->assertTrue($f->is($this->autoFieldTest__fieldType));
    }

    public function testEntityRestoring() {
        if (!isset($this->autoFieldTest__assoc)) return;

        $results = [];
        foreach ($this->autoFieldTest__assoc as $assoc) {
            $model = new Model();
            $model->fields->clear();
            $model->fields["field"] = new ($this->autoFieldTest__fieldType)();
            $entity = $model->restoreEntity($assoc, $model->getPopulateList());
            $results[] = $entity->toArray();
        }
        $this->assertMatchesJsonSnapshot($results);
    }

    public function testEntitySanitizing() {
        if (!isset($this->autoFieldTest__assoc)) return;

        $results = [];
        foreach ($this->autoFieldTest__assoc as $assoc) {
            $model = new Model();
            $model->fields->clear();
            $model->fields["field"] = new ($this->autoFieldTest__fieldType)();
            $entity = $model->restoreEntity($assoc, $model->getPopulateList());
            $results[] = $entity->sanitizeEntity();
        }
        $this->assertMatchesJsonSnapshot($results);
    }

    public function testInitQueries() {
        Query_CreateTable_Dummy::resetExecutedQueries();

        $model = new Model();
        $model->QueryBuilderTypes["select"] = Query_Select_Exception::class;
        $model->QueryBuilderTypes["create_table"] = Query_CreateTable_Dummy::class;
        $model->fields->clear();
        $model->fields["field"] = new ($this->autoFieldTest__fieldType)();

        $this->assertTrue($model->init());
        $this->assertMatchesSnapshot(implode("\n#", Query_CreateTable_Dummy::getExecutedQueries()));
        Query_CreateTable_Dummy::resetExecutedQueries();
    }
}
