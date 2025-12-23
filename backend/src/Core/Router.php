<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get(string $path, string $handler, bool $auth = false, bool $admin = false): void
    {
        $this->addRoute('GET', $path, $handler, $auth, $admin);
    }

    public function post(string $path, string $handler, bool $auth = false, bool $admin = false): void
    {
        $this->addRoute('POST', $path, $handler, $auth, $admin);
    }

    public function put(string $path, string $handler, bool $auth = false, bool $admin = false): void
    {
        $this->addRoute('PUT', $path, $handler, $auth, $admin);
    }

    public function delete(string $path, string $handler, bool $auth = false, bool $admin = false): void
    {
        $this->addRoute('DELETE', $path, $handler, $auth, $admin);
    }

    private function addRoute(string $method, string $path, string $handler, bool $auth, bool $admin): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'auth' => $auth,
            'admin' => $admin
        ];
    }

    public function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            $params = $this->matchRoute($route['path'], $uri);
            
            if ($route['method'] === $method && $params !== false) {
                // Auth middleware
                $userId = null;
                $userRole = null;
                
                if ($route['auth']) {
                    $authResult = $this->authenticate();
                    if ($authResult === null) {
                        $this->jsonResponse(['error' => 'Unauthorized'], 401);
                        return;
                    }
                    $userId = $authResult['userId'];
                    $userRole = $authResult['role'];
                    
                    // Admin check
                    if ($route['admin'] && $userRole !== 'admin') {
                        $this->jsonResponse(['error' => 'Forbidden - Admin access required'], 403);
                        return;
                    }
                }

                // Parse handler
                [$controllerName, $action] = explode('@', $route['handler']);
                $controllerClass = "App\\Controller\\{$controllerName}";

                if (!class_exists($controllerClass)) {
                    $this->jsonResponse(['error' => 'Controller not found'], 500);
                    return;
                }

                $controller = new $controllerClass($this->config);
                
                // Get request body
                $body = json_decode(file_get_contents('php://input'), true) ?? [];
                
                // Call controller method
                $result = $controller->$action($params, $body, $userId);
                
                $this->jsonResponse($result['data'], $result['status'] ?? 200);
                return;
            }
        }

        $this->jsonResponse(['error' => 'Route not found'], 404);
    }

    private function matchRoute(string $routePath, string $uri): array|false
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $uriParts = explode('/', trim($uri, '/'));

        if (count($routeParts) !== count($uriParts)) {
            return false;
        }

        $params = [];
        for ($i = 0; $i < count($routeParts); $i++) {
            if (preg_match('/^\{(\w+)\}$/', $routeParts[$i], $matches)) {
                $params[$matches[1]] = $uriParts[$i];
            } elseif ($routeParts[$i] !== $uriParts[$i]) {
                return false;
            }
        }

        return $params;
    }

    private function authenticate(): ?array
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return null;
        }

        $token = $matches[1];
        $jwt = new JWT($this->config['jwt']['secret']);
        
        try {
            $payload = $jwt->decode($token);
            return [
                'userId' => $payload['sub'],
                'role' => $payload['role'] ?? 'user'
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    private function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
