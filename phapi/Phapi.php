<?php

// Register auto loaging function
spl_autoload_register(function ($name) {
    $name = str_replace('\\', DS, $name);

    // Load file directly
    if (file_exists($name . '.php')) require_once($name . '.php');
    else if (file_exists($name . DS . 'index.php')) require_once($name . DS . 'index.php');

    // Load core module
    else if (file_exists('phapi' . DS . $name . '.php')) require_once('phapi' . DS . $name . '.php');
    else if (file_exists('phapi' . DS . $name . DS . 'index.php')) require_once('phapi' . DS . $name . DS . 'index.php');
});

// Global imports
require_once(ROOT . DS . 'phapi' . DS . 'functions.php');


// Phapi engine class
class Phapi {
    // Singleton factory
    private static ?Phapi $_instance = null;
    public static function instance() {
        if (self::$_instance == null)
            self::$_instance = new Phapi();
        return self::$_instance;
    }

    public Router $router;

    private function __construct() {
        $this->router = new Router();

        // Register JWT token processor middleware
        $this->router->addHandler(function ($p, $n) {
            return JWT::TokenParserMiddleware($p, $n);
        });
    }

    function serveApi($apiPrefix = "") {
        $this->router->addNamspace($apiPrefix . ":controller", "Routers\ApiRouter");
    }

    public function serveFolder($folder) {
        $this->router->addHandler(function ($p, $next) use ($folder) {
            $pf = realpath($folder);
            $fn = realpath($folder . DS .  implode(DS, $p["url"]));

            if (is_dir($fn)) {
                if (file_exists($fn . DS . "index.php")) $fn .= DS . "index.php";
                if (file_exists($fn . DS . "index.html")) $fn .= DS . "index.html";
            }

            if ($pf && $fn && str_starts_with($fn, $pf) && file_exists($fn)) {
                if (str_ends_with($fn, ".php")) {
                    include($fn);
                } else {
                    header('Content-Type: ' . mime_content_type($fn));
                    readfile($fn);
                }
            } else {
                $next();
            }
        });
    }

    public function serveFallbackFile($fn) {
        $this->router->addHandler(function ($p, $next) use ($fn) {
            if (file_exists($fn)) {
                header('Content-Type: ' . mime_content_type($fn));
                readfile($fn);
            } else $next();
        });
    }

    public function run() {
        // Explode url
        $url = isset($_GET['url']) ? explode('/', trim($_GET['url'], '/')) : [];

        // Compose parameters array
        $parameters = array();
        $parameters['method'] = $_SERVER['REQUEST_METHOD'];
        $parameters['url'] = $url;

        // Start router, redirect fallback to 404
        $this->router->run($parameters, function ($p, $next) {
            http_response_code(404);
        });
    }
}
