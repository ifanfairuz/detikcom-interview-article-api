<?php

namespace Core\Http;

use Core\Http\Exception\HttpException;
use Core\Http\Exception\HttpMethodNotAllowedException;
use Core\Http\Exception\HttpNotFoundException;

class Router
{
    protected array $routes;

    public function __construct(array &$routes)
    {
        $this->routes = &$routes;
    }

    public function handle(Request $req)
    {
        if ($route = @$this->routes[$req->uri]) {
            return $this->process($req, $route);
        }

        throw new HttpNotFoundException();
    }

    private function process(Request $req, array $route)
    {
        if (strtoupper($req->method) === 'OPTIONS') {
            $res = new Response();
            $res->configureCors($req, true, array_keys($route))->render();
            return;
        }

        if (!@$route[$req->method]) {
            throw new HttpMethodNotAllowedException();
        }

        $controllerClass = $route[$req->method][0];
        $controllerMethod = $route[$req->method][1];

        if (!class_exists($controllerClass)) {
            throw new HttpException("Controller class $controllerClass not found");
        }

        $controller = new $controllerClass;

        if (!method_exists($controller, $controllerMethod)) {
            throw new HttpException("Method $controllerMethod not found at class $controllerClass");
        }

        $res = $controller->$controllerMethod($req);
        $res = $res instanceof Response ? $res : (new Response())->json($res);
        $res->configureCors($req)->render();
    }
}
