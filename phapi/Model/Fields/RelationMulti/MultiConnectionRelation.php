<?php

namespace Model\Fields\RelationMulti;

use Model\Fields\RelationToOne;
use QueryBuilder\QB_Ref;
use QueryBuilder\QB_Const;

class MultiConnectionRelation extends RelationToOne {
  public function getFilter($fieldName, $prefix, $memberName, $sql_mode) {
    if ($sql_mode === 'SELECT') return [
      "or",
      [
        "and",
        ["=", new QB_Ref("table_1"), new QB_Const($this->getModel()->getTableName())],
        ["=", new QB_Ref("item_1"), new QB_Ref($memberName . ".id")]
      ],
      [
        "and",
        ["=", new QB_Ref("table_2"), new QB_Const($this->getModel()->getTableName())],
        ["=", new QB_Ref("item_2"), new QB_Ref($memberName . ".id")]
      ]
    ];
  }

  function useDefaultJoin() {
    return false;
  }
}
