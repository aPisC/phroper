<?php

class Config {
  public static array $database = [
    'user' => 'bendeguz',
    'password' => 'sqlpas',
    'server' => 'localhost',
    'port' => null,
    'database' => 'test'
  ];

  public static bool $serveFallbackAsIndex = true;
}
