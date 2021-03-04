<?php
  class Database{
    private static $config = array(
      'user' => 'bendeguz',
      'password' => 'sqlpas',
      'server' => 'localhost',
      'port' => null,
      'database' => 'test'
    );

    private static ?mysqli $_instance = null;
    public static function instance(){
      if(self::$_instance == null)
        self::$_instance = new mysqli(self::$config['server'], self::$config['user'], self::$config['password'], self::$config['database'], self::$config['port'] );
      return self::$_instance;
    }
  }
