<?php

include 'QueryBuilder.php';

$qb = new QueryBuilder(null, 'table');
$qb->addField('field1')->addField('field2');
$qb->setValues(array(null, 5));

$qb->where(function($qb) {
  $qb->where('field1', '<', 3)->andWhere('field2', 10);
})->orWhere(function($qb) {
  $qb->where('field1', '>', 3)->andWhere('field2', 5);
})->orWhere('field1 IS NULL');

var_dump($qb->getSelectQuery());
var_dump($qb->getSelectOneQuery());
var_dump($qb->getUpdateQuery());
var_dump($qb->getInsertQuery());