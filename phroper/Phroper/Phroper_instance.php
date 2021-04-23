<?php

namespace Phroper;

use Controllers\DefaultController;
use Error;
use Exception;
use mysqli;
use Phroper\Model\JsonModel;
use Services\DefaultService;

// Phroper engine class
class Phroper_instance {
    public Router $router;

    public function __construct() {
        $this->router = new Router();

        // Register JWT token processor middleware
        $this->router->addHandler(function ($p, $n) {
            return JWT::TokenParserMiddleware($p, $n);
        }, 1000);

        // Register internal plugins
        $this->registerPlugin('store', 'Phroper\\Plugin_Store');

        $this->router->addServeFolder("uploads/", ROOT . DS . "uploads");
    }

    public function run() {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')
            return;

        // Process url
        $url = $_GET['__url__'];
        unset($_GET['__url__']);
        if (str_ends_with($url, "/")) $url = str_drop_end($url, 1);
        if (str_starts_with($url, "/")) $url = substr($url, 1);

        // Compose parameters array
        $parameters = array();
        $parameters['method'] = $_SERVER['REQUEST_METHOD'];
        $parameters['url'] = $url;

        // Start router, redirect fallback to 404
        $this->router->run($parameters, function ($p) {
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
            throw new Exception("Mysqli must be set in phroper instance!");
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

    /* -----------------
    Cache
    ------------------- */
    private array $__cache = [];
    public function cache($item) {
        if ($item instanceof ICacheable && $item->isCacheable()) {
            $this->__cache[get_class($item)] = $item;
        }
    }

    /* ------------------
    Service, controller and model getter
    ------------------ */
    public function service($serviceName) {
        try {
            if ($serviceName instanceof Service)
                return $serviceName;
            $scn = 'Services\\' . str_kebab_pc($serviceName);
            if (isset($this->__cache[$scn]))
                return $this->__cache[$scn];
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
            $ccn = 'Controllers\\' . str_kebab_pc($controllerName);
            if (isset($this->__cache[$ccn]))
                return $this->__cache[$ccn];
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
            if (is_subclass_of($modelName, 'Phroper\Model'))
                return $modelName;
            // Loading by json
            if (is_string($modelName)) {
                if (isset($this->__cache["json:" . $modelName]))
                    return $this->__cache["json:" . $modelName];
                $jsonName = str_replace(["\\", "/"], DS, "/Models/" . str_kebab_pc($modelName));
                if (file_exists(ROOT . $jsonName . ".json")) {
                    $model = new JsonModel(ROOT . $jsonName . ".json");
                    $this->__cache["json:" . $modelName] = $model;
                    return $model;
                }
                if (file_exists(ROOT . DS . "phroper" . $jsonName . ".json")) {
                    $model = new JsonModel("phroper" . DS . $jsonName . ".json");
                    $this->__cache["json:" . $modelName] = $model;
                    return $model;
                }
            }
            // Loading by className
            $mcn = 'Models\\' . str_kebab_pc($modelName);
            if (isset($this->__cache[$mcn]))
                return $this->__cache[$mcn];
            $model = new $mcn();
            return $model;
        } catch (Error $e) {
            throw new Exception($e->getMessage());
        }
    }

    /* ------------------
    Content serving functions
    ------------------ */

    function serve($expression, $handler, $method = "*") {
        $this->router->add($expression, $handler, $method);
    }

    function serveApi($apiPrefix = "") {
        $this->router->add($apiPrefix . ":controller/", "Routers\ApiRouter");
    }

    public function serveFolder($folder) {
        $this->router->addHandler(function ($p, $next) use ($folder) {
            $pf = realpath($folder);
            $fn = realpath($folder . DS .  $p["url"]);

            if (is_dir($fn)) {
                if (file_exists($fn . DS . "index.html")) $fn .= DS . "index.html";
            }

            if ($pf && $fn && str_starts_with($fn, $pf) && file_exists($fn)) {
                header('Content-Type: ' . mime_content_type($fn));
                readfile($fn);
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
