<?php
namespace app\core;

class Router {
    private array $routes = [];

    public function add(string $method, string $path, callable $handler) {
        // Chuyển {id} thành regex nhóm bắt số
        $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '([0-9]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    public function dispatch(string $method, string $uri) {
        $uri = parse_url($uri, PHP_URL_PATH); // bỏ query string

        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($method) &&
                preg_match($route['pattern'], $uri, $matches)) {

                array_shift($matches); // bỏ match đầy đủ
                return call_user_func_array($route['handler'], $matches);
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }
}
