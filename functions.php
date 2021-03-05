<?php

function str_ends_with(string $haystack, string $needle) {
  $length = strlen($needle);
  if (!$length) return true;
  return substr($haystack, -$length) === $needle;
}

function str_starts_with(string $haystack, string $needle) {
  return substr($haystack, 0, strlen($needle)) === $needle;
}

function str_drop_end(string $haystack, int $count) {
  $length = strlen($haystack);
  return substr($haystack, 0, $length - $count);
}
