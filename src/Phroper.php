<?php

namespace Phroper;

use Exception;
use mysqli;
use Phroper\Controllers\DefaultController;
use Phroper\Model\JsonModel;
use Phroper\Services\DefaultService;
use Throwable;

class __Phroper__instance {
    public Router $router;

    public function __construct() {
        $this->__cache = [
            "Models\\AuthPermission" => "Phroper\\Models\\AuthPermission",
            "Models\\AuthRole" => "Phroper\\Models\\AuthRole",
            "Models\\AuthUser" => "Phroper\\Models\\AuthUser",
            "Models\\FileUpload" => "Phroper\\Models\\FileUpload",
            "Models\\Log" => "Phroper\\Models\\Log",
            "Models\\Store" => "Phroper\\Models\\Store",
            "Controllers\\Auth" => "Phroper\\Controllers\\Auth",
            "Controllers\\FileUpload" => "Phroper\\Controllers\\fileUpload",
            "Controllers\\Init" => "Phroper\\Controllers\\Init",
            "Controllers\\Role" => "Phroper\\Controllers\\Role",
            "Controllers\\User" => "Phroper\\Controllers\\User",
            "Services\\Auth" => "Phroper\\Services\\Auth",
            "Services\\Role" => "Phroper\\Services\\Role",
            "Services\\User" => "Phroper\\Services\\User",
            "Services\\Email" => "Phroper\\Services\\Email",
            "Services\\Log" => "Phroper\\Services\\Log",
        ];

        $this->router = new Router();

        // Register JWT token processor middleware
        $this->router->addHandler(function ($p, $n) {
            return JWT::TokenParserMiddleware($p, $n);
        }, 1000);

        $this->router->addServeFolder("/uploads/", Phroper::ini("ROOT") . DIRECTORY_SEPARATOR . "uploads");
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

        if ($this->backgroundTasks) {
            ignore_user_abort(true);
            if (is_callable('fastcgi_finish_request')) {
                session_write_close();
                fastcgi_finish_request();
            } else {

                header('Connection: close');
                header('Content-Length: ' . ob_get_length());
                ob_end_flush();
                //ob_flush();
                flush();
            }

            foreach ($this->backgroundTasks as $task) {
                try {
                    $task();
                } catch (Throwable $e) {
                    error_log("Background task error: " . $e);
                }
            }
        }
    }

    private array $backgroundTasks = [];
    public function addBackgroundTask($task) {
        $this->backgroundTasks[] = $task;
    }

    public function dir(...$args) {
        return Phroper::ini("ROOT") . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $args);
    }

    // ------------------
    // Mysqli
    // ------------------

    public function getMysqli() {
        return Phroper::ini("MYSQLI");
    }

    // ------------------
    // Context handler
    // ------------------
    private array $contextValues = [];

    public function setContext($name, $value) {
        $this->contextValues[$name] = $value;
    }

    public function context($name) {
        if (!isset($this->contextValues[$name]))
            return null;
        return $this->contextValues[$name];
    }

    // -----------------
    // Cache
    // -----------------
    private array $__cache = [];

    public function getCachedTypes() {
        return array_keys($this->__cache);
    }
    public function getCachedType($key) {
        if (isset($this->__cache[$key]))
            return $this->__cache[$key];
        return null;
    }

    public function cacheType($key, $value) {
        $key = implode("\\", str_kebab_pc(explode("\\", $key)));
        $this->__cache[$key] = $value;
    }

    // ------------------
    // Service, controller and model getter
    // ------------------
    public function service($serviceName) {
        if (is_subclass_of($serviceName, 'Phroper\\Service'))
            return $serviceName;

        if (!is_string($serviceName))
            throw new Exception("Service only can be loaded by name");

        $serviceName = str_kebab_pc($serviceName);

        // Test if the model is already cached
        if (isset($this->__cache["Services\\" . $serviceName]) && !is_string($this->__cache["Services\\" . $serviceName]))
            return $this->__cache["Services\\" . $serviceName];


        // Initialize by php file
        if (class_exists("Services\\" . $serviceName)) {
            $class = "Services\\" . $serviceName;
            $service = new $class();
            $this->__cache["Services\\" . $serviceName] = $service;
            return $service;
        }

        // Initialize by mapped type
        if (isset($this->__cache["Services\\" . $serviceName]) && is_string($this->__cache["Services\\" . $serviceName])) {
            $class = $this->__cache["Services\\" . $serviceName];
            $service = new $class();
            $this->__cache["Services\\" . $serviceName] = $service;
            return $service;
        }

        // Initialize default service
        $controller = new DefaultService($serviceName);
        $this->__cache["Services\\" . $serviceName] = $controller;
        return $controller;
    }

    public function controller($controllerName) {
        if (is_subclass_of($controllerName, 'Phroper\\Controller'))
            return $controllerName;

        if (!is_string($controllerName))
            throw new Exception("Controller only can be loaded by name");

        $controllerName = str_kebab_pc($controllerName);

        // Test if the model is already cached
        if (isset($this->__cache["Controllers\\" . $controllerName]) && !is_string($this->__cache["Controllers\\" . $controllerName]))
            return $this->__cache["Controllers\\" . $controllerName];


        // Initialize by php file
        if (class_exists("Controllers\\" . $controllerName)) {
            $class = "Controllers\\" . $controllerName;
            $controller = new $class();
            $this->__cache["Controllers\\" . $controllerName] = $controller;
            return $controller;
        }

        // Initialize by mapped type
        if (isset($this->__cache["Controllers\\" . $controllerName]) && is_string($this->__cache["Controllers\\" . $controllerName])) {
            $class = $this->__cache["Controllers\\" . $controllerName];
            $controller = new $class();
            $this->__cache["Controllers\\" . $controllerName] = $controller;
            return $controller;
        }

        // Initialize default controller
        $controller = new DefaultController($controllerName);
        $this->__cache["Controllers\\" . $controllerName] = $controller;
        return $controller;
    }


    private ?string $model_cache_key = null;
    public function model_cache_callback($model) {
        if ($this->model_cache_key)
            $this->__cache[$this->model_cache_key] = $model;
        $this->model_cache_key = null;
    }

    public function model($modelName) {
        try {
            if (is_subclass_of($modelName, 'Phroper\\Model'))
                return $modelName;

            if (!is_string($modelName))
                throw new Exception("Model only can be loaded by name");

            $modelName = str_kebab_pc($modelName);

            // Test if the model is already cached
            if (isset($this->__cache["Models\\" . $modelName]) && !is_string($this->__cache["Models\\" . $modelName]))
                return $this->__cache["Models\\" . $modelName];

            $basePath = Phroper::dir("Models", $modelName);

            // Initialize model by json schema
            if (file_exists($basePath . ".json")) {
                $this->model_cache_key = "Models\\" . $modelName;
                $model = new JsonModel($basePath . ".json");
                $this->__cache["Models\\" . $modelName] = $model;
                return $model;
            }

            // Initialize by class name
            if (class_exists("Models\\" . $modelName)) {
                $this->model_cache_key = "Models\\" . $modelName;
                $class = "Models\\" . $modelName;
                $model = new $class();
                $this->__cache["Models\\" . $modelName] = $model;
                return $model;
            }

            // initialize by mapped type
            if (isset($this->__cache["Models\\" . $modelName]) && is_string($this->__cache["Models\\" . $modelName])) {
                $this->model_cache_key = "Models\\" . $modelName;
                $class = $this->__cache["Models\\" . $modelName];
                $model = new $class();
                $this->__cache["Models\\" . $modelName] = $model;
                return $model;
            }

            throw new Exception("Model could not be found (" . $modelName . ")");
        } finally {
            $this->model_cache_key = null;
        }
    }

    // ------------------
    // Content serving functions
    // ------------------

    function serve($expression, $handler, $method = "*") {
        $this->router->add($expression, $handler, $method);
    }

    function serveApi($apiPrefix = "") {
        $this->router->add($apiPrefix . ":controller/", "Phroper\\Routers\\ApiRouter");
    }

    public function serveFolder($folder) {
        $this->router->addHandler(function ($p, $next) use ($folder) {
            $pf = realpath($folder);
            $fn = realpath($folder . DIRECTORY_SEPARATOR .  $p["url"]);

            if (is_dir($fn)) {
                if (file_exists($fn . DIRECTORY_SEPARATOR . "index.html")) $fn .=  DIRECTORY_SEPARATOR . "index.html";
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

class Phroper {

    // -------------------
    // Static members
    // -------------------

    private static ?array $_phroper_ini = null;

    public static function initialize(array $data) {
        if (self::$_phroper_ini)
            throw new Exception("Phroper is already initialized");

        if (isset($data["CONFIG_FILE"])) {
            try {
                $d2 = require($data["CONFIG_FILE"]);
                if ($d2 && is_array($d2)) $data = array_merge($d2, $data);
            } catch (Throwable $e) {
            }
        }

        self::$_phroper_ini = $data;

        if (self::$_instance == null)
            self::$_instance = new __Phroper__instance();
    }

    public static function ini($key) {
        if (isset(self::$_phroper_ini[$key]))
            return self::$_phroper_ini[$key];
        throw new Exception($key . " is not configured in Phroper::ini");
    }

    // Singleton factory
    private static ?__Phroper__instance $_instance = null;
    public static function instance() {
        return self::$_instance;
    }

    // CallStatic 
    public static function __callStatic($name, $arguments) {
        return Phroper::instance()->$name(...$arguments);
    }
}
