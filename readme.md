# Phroper

Headless CMS engine, written in php, written for you ❤.

## Startup configuration

In order to handle incoming request with Phroper, you have to redirect the request to a php file (for example: index.php).
If you are using apache server, write a .htaccess file an enable url rewriteing module.

Example content of .htaccess:

```
RewriteEngine On
RewriteRule ^([^?]*) index.php?:__url__=$1 [L,QSA]

<Limit GET POST PUT OPTIONS DELETE>
    Require all granted
</Limit>
<LimitExcept GET POST PUT OPTIONS DELETE>
    Require all denied
</LimitExcept>

```

In php file, you must provide the ROOT constant as the root of Phroper server, to handle dynamic imports correctly.
Make an instance of Phroper, register the handlers, and call run method.

Example of index.php:

```php
<?php

define('ROOT', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

require_once("phroper/index.php");

Phroper::setMysqli(new mysqli(
    "localhost",
    "user",
    "password",
    "database"
));

Phroper::serveApi("api/");
Phroper::serveFolder(ROOT . DS . "public");
Phroper::serveFallbackFile(ROOT . DS . "public" . DS . "index.html");

Phroper::run();

```

## Models

You can create new data schemas under Models/ and Phroper is able to handle php classes and json files for models. Classes have to be declared in Models namespace and has to be inherited from Phroper\Model.

### Model parameters

These parameters can be configured to scecify the beavior of the model. When declaring a php class, these parameters are can be passed to Model class at first parameter of Model constructor as an assoc array, in JSON models these parameters are set in the root of the JSON object.

| Parameter name  | Description                                                   | Default              |
| --------------- | ------------------------------------------------------------- | -------------------- |
| display         | This field will be displayed on ui to identify the entity     | `id`                 |
| primary         | Primary key of the model                                      | `id`                 |
| visible         | Determinates if the model is displayed on the admin panel     | `true`               |
| editable        | Determinates if the data can be edited through admin panel    | `true`               |
| key             | Identifier of the model, do not change it                     | auto generated id    |
| name            | Name of the model, will be displayed on admin panel           | Key as readable text |
| sql_table       | Model will use this sql table in the background               | Key of the model     |
| default_service | If true, the model will be editable through default Rest apis | `true`               |

### Fields of model

The fields determinates the structure of an entity. The models have 4 predefined fields, but any of them can be overwritten or can be removed by setting then to null. In a php class model the fields can be accessed and modified throug the $fields member like an assoc array and have to give an instance of a Field type, in a JSON model the fields are set by the members of the fields object, is it possible to specify the class name of the field as string, or specify an array (classname followed by constructor arguments).

### Examples

These examples showing an equivalent model declaration with php and json structure;

```php
// Models/Logs.php
namespace Models;


use Phroper\Model;
use Phroper\Model\Fields\Email;
use Phroper\Model\Fields\Password;

class Log extends Model {
  public function __construct() {
    parent::__construct([
      "sql_table" => "log",
      "editable" => false,
      "default_service" => false
    ]);

    $this->fields["updated_at"] = null;
    $this->fields["type"] = new Phroper\Model\Fields\Enum(["debug", "info", "warn", "error"]);
    $this->fields["message"] = new Phroper\Model\Fields\Text();
  }
}


```

```json
// Models/Log.json
{
  "sql_table": "log",
  "editable": false,
  "default_service": false,
  "fields": {
    "updated_at": null,
    "type": ["Enum", ["debug", "info", "warn", "error"]],
    "message": "Text"
  }
}
```

# Fields

The field parameters can be passed as the last argument of field constructor. All field type is inherited from Phroper\Model\Fields\Field. The base field parameters can be found in the following table.

| Parameter name                               | Description                                                                            | Default                         |
| -------------------------------------------- | -------------------------------------------------------------------------------------- | ------------------------------- |
| auto                                         | Indicates that the field is auto generated, can not be inserted or updated             | `false`                         |
| type                                         | determinates the editor type on admin panel                                            | "text"                          |
| default                                      | Default value of the field                                                             | `IgnoreField::instance()`       |
| sql_field                                    | SQL column name of the field                                                           | key of the field in fields list |
| sql_type                                     | type of sql column                                                                     |                                 |
| sql_length                                   | Length of sql type                                                                     |                                 |
| sql_primary                                  | Indicates if the field is primary key                                                  | `false`                         |
| sql_autoincrement                            | Indicates if the field is auto incremented                                             | `false`                         |
| sql_extra                                    | Additional parameters for column definition                                            |                                 |
| sql_unsigned                                 | Marks the db column as unsigned                                                        | `false`                         |
| forced                                       | The field will be updated always whit null if no data provided                         | `false`                         |
| private                                      | If true, the field will be not available through rest api                              | `false`                         |
| populate                                     | If true, the field will be default populated and joined, when joining is not specified |
| readonly                                     | The field can not be updated if true                                                   | `false`                         |
| required                                     | If true, the value can not be empty and needs to be inserted                           | `false`                         |
| sql_length                                   | Length of sql type                                                                     |                                 |
| unique                                       | If true, sql table can not contain two entity with same column value                   | `false`                         |
| virtual                                      | Indicates that the value is calculated and not sored in sql table                      | `false`                         |
| regex (Text)                                 | Text field value will be matched the given pattern before saving                       |                                 |
| via (RelationToMany)                         | This field will determinate what is the connected field in a One To Many relation      |                                 |
| min (Integer)                                | Specifies the minimum value of a number                                                |                                 |
| max (Integer)                                | Specifies the maximum value of aninteger                                               |                                 |
| sql_disable_constraint (Enum, RelationToOne) | Disables constraint creation                                                           | `false`                         |
| sql_delete_action (RelationToOne)            | Delete action of the relation                                                          | RESTRICT                        |

### Available field types

| Field type      | Description                                                                                                                                 | Base type     |
| --------------- | ------------------------------------------------------------------------------------------------------------------------------------------- | ------------- |
| Field           | Base type of all fields                                                                                                                     | -             |
| Boolean         | Logical field, can store true / false                                                                                                       | Field         |
| Text            | Text field (VARCHAR)                                                                                                                        | Field         |
| Email           | Text field that valdates email format                                                                                                       | Text          |
| Integer         | Number field (INT)                                                                                                                          | Field         |
| Identity        | Auto incremented primary key (UNSIGNED INTEGER)                                                                                             | Field         |
| Password        | Text field where only the hash is stored, only updates if some value is provided                                                            | Text          |
| TextKey         | Text field with primary key settings                                                                                                        | Text          |
| Enum            | Text field that can only store predefined values (first ctor parameter)                                                                     | Text          |
| Timestamp       | Field to store date and time (TIMESTAMP)                                                                                                    | Field         |
| CreatedAt       | Wrapper of Timestamp to store entity creation time                                                                                          | Timestamp     |
| UpdatedAt       | Wrapper of Timestamp to store entity update time                                                                                            | Timestamp     |
| Relation        | Base type of relation fields, additional constructor argument: name of connected model                                                      | Relation      |
| RelationToOne   | Stores the id of the connected entity (UNSIGNED INTEGER).                                                                                   | Relation      |
| RelationToMany  | This field will select all connected entities by a field specified in connected model (via parameter).                                      | Relation      |
| UpdatedBy       | Relation that automatically stores the current user id, related to AuthUser (can be null, without foreign key definition)                   | RelationToOne |
| RelationMulti   | More - More relation between two models, relation table name will be generated from the table names of models and the relationKey parameter | Relation      |
| FileUpload      | Relation to FileUpload model                                                                                                                | RelationToOne |
| FileUploadMulti | Multi relation to FileUpload model                                                                                                          | RelationMulti |
| Json            | This field is able to store complex json data (TEXT), encoded and decoded automatically                                                     | Field         |
