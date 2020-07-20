
<?php
  $c = include(dirname(__FILE__).'/../config/db.php');
  return mysqli_connect($c['server'], $c['user'], $c['password'], $c['database']);
?>