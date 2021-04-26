<?php

namespace Phroper;

use Controllers\DefaultController;
use Exception;
use mysqli;
use Phroper\Model\JsonModel;
use Services\DefaultService;

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
        ];

        $this->router = new Router();

        // Register JWT token processor middleware
        $this->router->addHandler(function ($p, $n) {
            return JWT::TokenParserMiddleware($p, $n);
        }, 1000);

        // Register internal plugins
        $this->registerPlugin('store', 'Phroper\\Plugin_Store');

        $this->router->addServeFolder("uploads/", Phroper::ini("ROOT") . Phroper::ini("DS") . "uploads");
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

    // ------------------
    // Mysqli
    // ------------------
    private ?mysqli $mysqli = null;

    public function setMysqli($mysqli) {
        $this->mysqli = $mysqli;
    }

    public function getMysqli() {
        if ($this->mysqli == null)
            throw new Exception("Mysqli must be set in phroper instance!");
        return $this->mysqli;
    }

    // ------------------
    // Plugin handler
    // ------------------
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
    public function cache($item) {
        // TODO
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
    public function model_cache_callback($model){
        if($this->model_cache_key)
            $this->__cache[$this->model_cache_key] = $model;
        $this->model_cache_key = null;    
    }

    public function model($modelName) {
        try{
        if (is_subclass_of($modelName, 'Phroper\\Model'))
            return $modelName;

        if (!is_string($modelName))
            throw new Exception("Model only can be loaded by name");

        $modelName = str_kebab_pc($modelName);

        // Test if the model is already cached
        if (isset($this->__cache["Models\\" . $modelName]) && !is_string($this->__cache["Models\\" . $modelName]))
            return $this->__cache["Models\\" . $modelName];

        $basePath = implode(Phroper::ini("DS"), [Phroper::ini("ROOT"), "Models", $modelName]);

        // Initialize model by json schema
        if (file_exists($basePath . ".json")) {
            $this->model_cache_key= "Models\\" . $modelName;
            $model = new JsonModel($basePath . ".json");
            $this->__cache["Models\\" . $modelName] = $model;
            return $model;
        }

        // Initialize by class name
        if (class_exists("Models\\" . $modelName)) {
            $this->model_cache_key= "Models\\" . $modelName;
            $class = "Models\\" . $modelName;
            $model = new $class();
            $this->__cache["Models\\" . $modelName] = $model;
            return $model;
        }

        // initialize by mapped type
        if (isset($this->__cache["Models\\" . $modelName]) && is_string($this->__cache["Models\\" . $modelName])) {
            $this->model_cache_key= "Models\\" . $modelName;
            $class = $this->__cache["Models\\" . $modelName];
            $model = new $class();
            $this->__cache["Models\\" . $modelName] = $model;
            return $model;
        }

        throw new Exception("Model could not be found (" . $modelName . ")");
        }
        finally{
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
        $this->router->add($apiPrefix . ":controller/", "Routers\ApiRouter");
    }

    public function serveFolder($folder) {
        $this->router->addHandler(function ($p, $next) use ($folder) {
            $pf = realpath($folder);
            $fn = realpath($folder . Phroper::ini("DS") .  $p["url"]);

            if (is_dir($fn)) {
                if (file_exists($fn . Phroper::ini("DS") . "index.html")) $fn .=  Phroper::ini("DS") . "index.html";
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
            self::$_phroper_ini = $data;

        if (self::$_instance == null)
            self::$_instance = new __Phroper__instance();
    }

    public static function ini($key) {
        if (isset(self::$_phroper_ini[$key]))
            return self::$_phroper_ini[$key];
        return null;
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