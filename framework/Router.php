<?php

class Route {
    public string $route_regexp;
    public $controller;
    public array $middlewareList = [];

    public function middleware(BaseMiddleware $m) : Route {
        array_push($this->middlewareList, $m);
        return $this;
    }

    public function __construct($route_regexp, $controller) {
        $this->route_regexp = $route_regexp;
        $this->controller = $controller;
    }
}

class Router {
    /**
     * @var Route[]
     */
    protected $routes = [];

    protected $twig;
    protected $pdo;

    public function __construct($twig, $pdo) {
        $this->twig = $twig;
        $this->pdo = $pdo;
    }

    public function add($route_regexp, $controller) : Route {
        $route = new Route("#^$route_regexp$#", $controller);
        array_push($this->routes, $route);

        return $route;
    }

    public function get_or_default($default_controller) {
        $url = $_SERVER["REQUEST_URI"]; 

        $path = parse_url($url, PHP_URL_PATH);

        $controller = $default_controller;
        $newRoute = null;

        $matches = [];
        foreach($this->routes as $route) {
            if (preg_match($route->route_regexp, $path, $matches)) {
                $controller = $route->controller;
                $newRoute = $route;
                break;
            }
        }

        $controllerInstance = new $controller();
        $controllerInstance->setPDO($this->pdo);
        $controllerInstance->setParams($matches);

        if ($controllerInstance instanceof TwigBaseController) {
            $controllerInstance->setTwig($this->twig);
        }

        if($newRoute) {
            foreach ($newRoute->middlewareList as $m) {
                $m->apply($controllerInstance, []);
            }
        }

        return $controllerInstance->process_response();
    }
}