<?php
  function startsWith ($string, $startString) 
  { 
      $len = strlen($startString); 
      return (substr($string, 0, $len) === $startString); 
  } 

  function endsWith($string, $endString) 
  { 
      $len = strlen($endString); 
      if ($len == 0) { 
          return true; 
      } 
      return (substr($string, -$len) === $endString); 
  } 

  function json_load_body(){
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);
    return $input;
  }
?>