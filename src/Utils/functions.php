<?php


if (!is_callable("str_ends_with")) {
  function str_ends_with(string $haystack, string $needle) {
    $length = strlen($needle);
    if (!$length) return true;
    return substr($haystack, -$length) === $needle;
  }
}

if (!is_callable("str_pc_text")) {
  function str_pc_text($name) {
    if (is_array($name)) return array_map("str_pc_text", $name);
    $matches = [];
    while (preg_match("/[a-z][A-Z]/", $name, $matches)) {
      $name = str_replace($matches[0], substr($matches[0], 0, 1) . " " . substr($matches[0], 1, 1), $name);
    }
    return ucfirst($name);
  }
}


if (!is_callable("str_pc_kebab")) {
  function str_pc_kebab($name) {
    if (is_array($name)) return array_map("str_pc_kebab", $name);
    $matches = [];
    while (preg_match("/[a-z][A-Z]/", $name, $matches)) {
      $name = str_replace($matches[0], substr($matches[0], 0, 1) . "-" .  substr($matches[0], 1, 1), $name);
    }
    return strtolower($name);
  }
}

if (!is_callable("str_kebab_pc")) {
  function str_kebab_pc($name) {
    if (is_array($name)) return array_map("str_kebab_pc", $name);
    $matches = [];
    while (preg_match("/[a-z]-[a-z]/", $name, $matches)) {
      $name = str_replace($matches[0], substr($matches[0], 0, 1) .  ucfirst(substr($matches[0], 2, 1)), $name);
    }
    return ucfirst($name);
  }
}


if (!is_callable("str_text_pc")) {
  function str_text_pc($name) {
    if (is_array($name)) return array_map("str_text_pc", $name);
    $matches = [];
    while (preg_match("/[a-z] [a-zA-Z]/", $name, $matches)) {
      $name = str_replace($matches[0], substr($matches[0], 0, 1) .  ucfirst(substr($matches[0], 2, 1)), $name);
    }
    return ucfirst($name);
  }
}

if (!is_callable("str_starts_with")) {
  function str_starts_with(string $haystack, string $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
  }
}


if (!is_callable("str_drop_end")) {
  function str_drop_end(string $haystack, int $count) {
    $length = strlen($haystack);
    return substr($haystack, 0, $length - $count);
  }
}


if (!is_callable("str_suffix")) {
  function str_suffix($str, $suffix, $delim = ".") {
    $str = $str ? $str : "";
    $suffix = $suffix ? $suffix : "";

    if (!$suffix)
      return $str;
    if (!$str)
      return $suffix;
    return $str . $delim . $suffix;
  }
}


if (!is_callable("json_load_body")) {
  function json_load_body() {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, TRUE);
    return $input;
  }
}
