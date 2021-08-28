<?php

namespace Phroper\Models;

use Phroper\Fields\CreatedAt;
use Phroper\Fields\Enum;
use Phroper\Fields\Text;
use Phroper\Fields\UpdatedBy;
use Phroper\Model;

class Log extends Model {
  public function __construct() {
    parent::__construct([
      'table' => "log",
      "editable" => false,
      "sort" => "created_at:desc",
      "listing" => [
        "id",
        "type",
        "message",
        "created_at",
        "updated_by",
        "remote_address",
      ]
    ]);

    $this->fields['updated_at'] = null;
    $this->fields["updated_by"] = new UpdatedBy(["listed"]);
    $this->fields["created_at"] = new CreatedAt(["listed"]);
    $this->fields['type'] = new Enum([
      "debug",
      "info",
      "warn",
      "error",
    ]);
    $this->fields['message'] = new Text(["sql_length" => 1024, "sql_truncate" => true]);
    $this->fields['remote_address'] = new Text([
      "default" => function () {
        return $_SERVER["REMOTE_ADDR"];
      }
    ]);
  }

  public function allowDefaultService(): bool {
    return false;
  }
}
