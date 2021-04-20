<?php

namespace Phapi\Model\Fields\RelationMulti;

use Phapi\Model\Fields\RelationToOne;
use QueryBuilder\QB_Ref;
use QueryBuilder\QB_Const;

class MultiConnectionRelation extends RelationToOne {

  function useDefaultJoin() {
    return false;
  }
}
