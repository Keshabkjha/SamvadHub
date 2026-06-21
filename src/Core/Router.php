<?php

namespace App\Core;

class Router {
    private array $routes = [];

    /**
     * Register a GET route.
     */
    public function get(string $path, array $callback): void {
        $this->routes['GET'][$path] = $callback;
    }

    /**
     * Register a POST route.
     */
    public function post(string $path, array $callback): void {
        $this->routes['POST'][$path] = $callback;
    }

    /**
     * Resolve the current request path to a registered route.
     */
    public function resolve(Request $request): void {
        $method = $request->getMethod();
        $path = $request->getPath();

        $routesForMethod = $this->routes[$method] ?? [];

        foreach ($routesForMethod as $routePath => $callback) {
            // Convert route format like '/profile/{username}' to regex: '^/profile/(?P<username>[^/]+)$'
            $pattern = '@^' . preg_replace('/\\\{([a-zA-Z0-9_]+)\\\}/', '(?P<$1>[^/]+)', preg_quote($routePath, '@')) . '$@';

            if (preg_match($pattern, $path, $matches)) {
                // Keep only string keys from the match array (corresponds to named params)
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $request->setRouteParams($params);

                [$controllerClass, $action] = $callback;
                
                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    if (method_exists($controller, $action)) {
                        $controller->$action($request);
                        return;
                    }
                }
                
                if (APP_ENV === 'development') {
                    die("Controller or action not found: {$controllerClass}::{$action}");
                }
            }
        }

        // If no route matches
        if ($request->isAjax()) {
            Response::json(['status' => false, 'error' => 'Endpoint not found'], 404);
        } else {
            http_response_code(404);
            Response::renderView('404', ['page_title' => 'Page Not Found']);
        }
    }
}
