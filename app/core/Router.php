<?php

namespace Core;

use Symfony\Component\Yaml\Parser;

class Router
{

    public static function initRouter()
    {

        $router = new \AltoRouter();

        $yaml = new Parser();

        $routes = $yaml->parse(file_get_contents('config/routes.yaml'));

        foreach ($routes as $name => $route) {
            $router->map($route['method'], $route['route'], array('c' => $route['controller'], 'a' => $route['action']), $name);
        }

        $match = $router->match();

        if ($match && method_exists($match['target']['c'], $match['target']['a'])) {
            $controller = new $match['target']['c'];
            call_user_func_array(array($controller, $match['target']['a']), $match['params']);
        } else {
            // No route was matched
            header('Location: /404');
        }
    }
}
