<?php

function str_ends_with(string $haystack, string $needle) {
  $length = strlen($needle);
  if (!$length) return true;
  return substr($haystack, -$length) === $needle;
}

function str_pc_text($name) {
  $matches = [];
  while (preg_match("/[a-z][A-Z]/", $name, $matches)) {
    $name = str_replace($matches[0], substr($matches[0], 0, 1) . " " . substr($matches[0], 1, 1), $name);
  }
  return ucfirst($name);
}

function str_pc_kebab($name) {
  $matches = [];
  while (preg_match("/[a-z][A-Z]/", $name, $matches)) {
    $name = str_replace($matches[0], substr($matches[0], 0, 1) . "-" .  substr($matches[0], 1, 1), $name);
  }
  return strtolower($name);
}

function str_kebab_pc($name) {
  $matches = [];
  while (preg_match("/[a-z]-[a-z]/", $name, $matches)) {
    $name = str_replace($matches[0], substr($matches[0], 0, 1) .  ucfirst(substr($matches[0], 2, 1)), $name);
  }
  return ucfirst($name);
}

function str_text_pc($name) {
  $matches = [];
  while (preg_match("/[a-z] [a-zA-Z]/", $name, $matches)) {
    $name = str_replace($matches[0], substr($matches[0], 0, 1) .  ucfirst(substr($matches[0], 2, 1)), $name);
  }
  return ucfirst($name);
}

function str_starts_with(string $haystack, string $needle) {
  return substr($haystack, 0, strlen($needle)) === $needle;
}

function str_drop_end(string $haystack, int $count) {
  $length = strlen($haystack);
  return substr($haystack, 0, $length - $count);
}

function str_suffix($str, $suffix, $delim = ".") {
  $str = $str ? $str : "";
  $suffix = $suffix ? $suffix : "";

  if (!$suffix)
    return $str;
  if (!$str)
    return $suffix;
  return $str . $delim . $suffix;
}

function json_load_body() {
  $inputJSON = file_get_contents('php://input');
  $input = json_decode($inputJSON, TRUE);
  return $input;
}
