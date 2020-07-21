<?php
  class Router{

    private $routes = Array();
    private $parameters;
  
    public function __construct($parameters = array()) {
      $this->parameters = $parameters;
    }

    public function add($expression, $function, $method = '*', $isNamespace = false){
      array_push($this->routes,Array(
        'expression' => $expression,
        'function' => $function,
        'method' => $method,
        'isNamespace' => $isNamespace
      ));
    }
    public function addNamspace($expression, $function){
      array_push($this->routes,Array(
        'expression' => $expression,
        'function' => $function,
        'method' => '*',
        'isNamespace' => true
      ));
    }
  
    public function run($url, $parameters = array()){
      // Get current request method
      $method = $_SERVER['REQUEST_METHOD'];
  
      foreach($this->routes as $route){
        if($route['method'] != '*' && $route['method'] != $method && (!is_array( $route['method']) || !in_array($method,  $route['method'])) )
          continue;

        $routeParts = $route['expression'] != null ? explode('/', $route['expression']) : [];
        $isMatching = true;
        $parameters['method'] = $method;

        $i = 0;
        for(; $i < count($routeParts); $i++ ){
          if($i >= count($url)){
            $isMatching = false;
            break;
          }
          // Remaining mathcing parameter
          if( startsWith( $routeParts[$i], '::')){
            $pname = substr( $routeParts[$i], 2);
            $parameters[$pname] = join('/', 
              array_filter($url, function($v, $k) use ($i) { return $k >= $i; } , ARRAY_FILTER_USE_BOTH)
            );
            $i = count($url);
            break;
          }
          // One matching parameter
          else if( startsWith( $routeParts[$i], ':')){
            $pname = substr( $routeParts[$i], 1);
            $parameters[$pname] = $url[$i];
          }
          else if ($url[$i] != $routeParts[$i]){
            $isMatching = false;
            break;
          }
        }

        // Reject if not a namespace route and not he whole url processed
        if(!$route['isNamespace'] && $i != count($url)){
          $isMatching = false;
        }

        if($isMatching){
          $u = array_values(
            array_filter($url, function($v, $k) use ($i) { return $k >= $i; } , ARRAY_FILTER_USE_BOTH)
          );
          $p = array_merge($this->parameters, $parameters);

          if(is_callable($route['function'])){
            $route['function']($u, $p);
          }
          else if ($route['function'] instanceof Router) {
            $route['function']->run($u, $p);
          }
          else if (class_exists($route['function'])){
            $r = new $route['function']($p);
            $r->run($u, $p);
          }
          break;
        }
      }

    }
  }
?>