<?php

use Phapi\Phapi_instance;

class Phapi {
    // Singleton factory
    private static ?Phapi_instance $_instance = null;
    public static function instance() {
        if (self::$_instance == null)
            self::$_instance = new Phapi_instance();
        return self::$_instance;
    }

    // CallStatic 
    public static function __callStatic($name, $arguments) {
        return Phapi::instance()->$name(...$arguments);
    }
}
