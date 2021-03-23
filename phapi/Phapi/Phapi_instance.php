<?php

namespace Phapi;

use DefaultController;
use DefaultService;
use Error;
use Exception;
use mysqli;

// Phapi engine class
class Phapi_instance {
    public Router $router;

    public function __construct() {
        $this->router = new Router();

        // Register JWT token processor middleware
        $this->router->addHandler(function ($p, $n) {
            return JWT::TokenParserMiddleware($p, $n);
        });

        // Register internal plugins
        $this->registerPlugin('store', 'Phapi\\Plugin_Store');
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

    /* ------------------
    Mysqli
    ------------------ */
    private ?mysqli $mysqli = null;

    public function setMysqli($mysqli) {
        $this->mysqli = $mysqli;
    }

    public function getMysqli() {
        if ($this->mysqli == null)
            throw new Exception("Mysqli must be set in phapi instance!");
        return $this->mysqli;
    }

    /* ------------------
    Plugin handler
    ------------------ */
    private array $plugins = [];

    public function registerPlugin($name, $obj) {
        $this->plugins[$name] = $obj;
        return $obj;
    }

    public function plugin($name) {
        $plugin = $this->plugins[$name];
        if (is_string($plugin) && class_exists($plugin))
            return $this->registerPlugin($name, new $plugin());
        return  $plugin;
    }

    /* ------------------
    Context handler
    ------------------ */
    private array $contextValues = [];

    public function setContext($name, $value) {
        $this->contextValues[$name] = $value;
    }

    public function context($name) {
        if (!isset($this->contextValues[$name]))
            return null;
        return $this->contextValues[$name];
    }

    /* ------------------
    Service, controller and model getter
    ------------------ */
    public function service($serviceName) {
        try {
            if ($serviceName instanceof Service)
                return $serviceName;
            $scn = 'Services\\' . ucfirst($serviceName);
            if (class_exists($scn)) $service = new $scn();
            else $service = new DefaultService($serviceName);
            return $service;
        } catch (Error $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function controller($controllerName) {
        try {
            if ($controllerName instanceof Controller)
                return $controllerName;
            $ccn = 'Controllers\\' . ucfirst($controllerName);
            if (class_exists($ccn))
                $controller = new $ccn();
            else {
                $controller = new DefaultController($controllerName);
            }
            return $controller;
        } catch (Error $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function model($modelName) {
        try {
            if (is_subclass_of($modelName, 'Phapi\Model'))
                return $modelName;
            $mcn = 'Models\\' . ucfirst($modelName);
            $model = new $mcn();
            return $model;
        } catch (Error $e) {
            throw new Exception($e->getMessage());
        }
    }

    /* ------------------
    Content serving functions
    ------------------ */

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
}
