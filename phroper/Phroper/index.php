<?php

use Phroper\Phroper_instance;

class Phroper {
    // Singleton factory
    private static ?Phroper_instance $_instance = null;
    public static function instance() {
        if (self::$_instance == null)
            self::$_instance = new Phroper_instance();
        return self::$_instance;
    }

    // CallStatic 
    public static function __callStatic($name, $arguments) {
        return Phroper::instance()->$name(...$arguments);
    }
}
