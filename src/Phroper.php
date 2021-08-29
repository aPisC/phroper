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
    public Injector $injector;
    public Context $context;

    private array $preRequestTasks = [];
    private array $postRequestTasks = [];
    private array $backgroundTasks = [];

    public function __construct() {
        $this->context = new Context();
        $this->router = new Router();
        $this->injector = new Injector();
    }

    public function run() {

        // if (ob_get_level() == 0)
        ob_start();

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
        $this->context->set("url", $url);
        $this->context->set("method", $_SERVER['REQUEST_METHOD']);



        // Execute preRequest tasks
        foreach ($this->preRequestTasks as  $task) {
            $task();
        }

        // Start router
        $this->router->run($parameters, function ($p) {
            // Redirect to error 404 when the request is unhandled
            http_response_code(404);
        });

        // Execute postRequest tasks
        foreach ($this->postRequestTasks as  $task) {
            $task();
        }

        // Executing background tasks
        if ($this->backgroundTasks) {
            ignore_user_abort(true);
            if (is_callable('fastcgi_finish_request')) {
                session_write_close();
                fastcgi_finish_request();
            } else {
                if (ob_get_level() > 0) {
                    header('Connection: close');
                    header('Content-Length: ' . ob_get_length());
                    ob_end_flush();
                }
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

    // Task registering functions
    public function addBackgroundTask($task) {
        $this->backgroundTasks[] = $task;
    }

    public function addPreRequestTask($task) {
        $this->preRequestTasks[] = $task;
    }

    public function addPostRequestTask($task) {
        $this->postRequestTasks[] = $task;
    }

    public function dir(...$args) {
        return Phroper::ini("ROOT") . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $args);
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
        if ($this->injector->hasEntityCached("Services\\" . $serviceName))
            return $this->injector->tryInstantiate("Services\\" . $serviceName);

        // Initialize by php file
        if (class_exists("Services\\" . $serviceName)) {
            $class = "Services\\" . $serviceName;
            $service = new $class();
            $this->injector->provideEntity("Services\\" . $serviceName, $service);
            return $service;
        }

        // Initialize from injector
        if ($this->injector->hasType("Services\\" . $serviceName))
            return $this->injector->instantiate("Services\\" . $serviceName);

        // Initialize default service
        $service = new DefaultService($serviceName);
        $this->injector->provideEntity("Services\\" . $serviceName, $service);
        return $service;
    }

    public function controller($controllerName) {
        if (is_subclass_of($controllerName, 'Phroper\\Controller'))
            return $controllerName;

        if (!is_string($controllerName))
            throw new Exception("Controller only can be loaded by name");

        $controllerName = str_kebab_pc($controllerName);

        // Test if the controller is already cached
        if ($this->injector->hasEntityCached("Controllers\\" . $controllerName))
            return $this->injector->tryInstantiate("Controllers\\" . $controllerName);


        // Initialize by php file
        if (class_exists("Controllers\\" . $controllerName)) {
            $class = "Controllers\\" . $controllerName;
            $controller = new $class();
            $this->injector->provideEntity("Controllers\\" . $controllerName, $controller);
            return $controller;
        }

        // Initialize from injector
        if ($this->injector->hasType("Controllers\\" . $controllerName))
            return $this->injector->instantiate("Controllers\\" . $controllerName);

        // Initialize default controller
        $controller = new DefaultController($controllerName);
        $this->injector->provideEntity("Controllers\\" . $controllerName,  $controller);
        return $controller;
    }


    private ?string $model_cache_key = null;
    public function model_cache_callback($model) {
        if ($this->model_cache_key)
            $this->injector->provideEntity($this->model_cache_key, $model);
        $this->model_cache_key = null;
    }

    public function model($modelName) {
        try {
            if (is_subclass_of($modelName, 'Phroper\\Model') || $modelName instanceof Model)
                return $modelName;

            if (!is_string($modelName))
                throw new Exception("Model only can be loaded by name");

            $modelName = str_kebab_pc($modelName);

            // Test if the model is already cached
            if ($this->injector->hasEntityCached("Models\\" . $modelName))
                return $this->injector->tryInstantiate("Models\\" . $modelName);

            $basePath = Phroper::dir("Models", $modelName);

            // Initialize model by json schema
            if (file_exists($basePath . ".json")) {
                $this->model_cache_key = "Models\\" . $modelName;
                $model = new JsonModel($basePath . ".json");
                $this->injector->provideEntity("Models\\" . $modelName, $model);
                return $model;
            }

            // Initialize by php file
            if (class_exists("Models\\" . $modelName)) {
                $this->model_cache_key = "Models\\" . $modelName;
                $class = "Models\\" . $modelName;
                $model = new $class();
                $this->injector->provideEntity("Models\\" . $modelName, $model);
                return $model;
            }

            // Initialize from injector
            if ($this->injector->hasType("Models\\" . $modelName)) {
                $this->model_cache_key = "Models\\" . $modelName;
                return $this->injector->tryInstantiate("Models\\" . $modelName);
            }
        } finally {
            $this->model_cache_key = null;
        }
    }
}

class Phroper {
    private static ?array $_phroper_ini = null;
    private static array $_init_hooks = [];
    private static ?__Phroper__instance $_instance = null;

    public static function addInitializer($fn) {
        self::$_init_hooks[] = $fn;

        if (self::$_phroper_ini)
            $fn();
    }

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

        foreach (self::$_init_hooks as $hook) {
            $hook();
        }
    }

    public static function reinitialize(array $data) {
        self::$_instance = null;
        self::$_phroper_ini = null;
        self::initialize($data);
    }

    public static function ini($key) {
        if (array_key_exists($key, self::$_phroper_ini))
            return self::$_phroper_ini[$key];
        throw new Exception($key . " is not configured in Phroper::ini");
    }

    // Singleton factory
    public static function instance() {
        return self::$_instance;
    }

    // Magic methods
    public static function __callStatic($name, $arguments) {
        if (!self::$_instance)
            throw new Exception("Phroper instance is not initialized.");
        return self::$_instance->$name(...$arguments);
    }
}
