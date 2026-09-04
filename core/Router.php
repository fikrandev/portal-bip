<?php
/**
 * Portal BIP - Simple Router
 * 
 * Maps URL paths to controller actions.
 * Supports GET and POST methods with middleware.
 */

class Router
{
    private array $routes = [];
    private string $basePath = '';

    /**
     * Constructor
     * 
     * @param string $basePath  Base URL path prefix to strip (e.g., '/portal-bip')
     */
    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Register a GET route
     */
    public function get(string $path, callable $handler, array $middleware = []): self
    {
        $this->addRoute('GET', $path, $handler, $middleware);
        return $this;
    }

    /**
     * Register a POST route
     */
    public function post(string $path, callable $handler, array $middleware = []): self
    {
        $this->addRoute('POST', $path, $handler, $middleware);
        return $this;
    }

    /**
     * Register both GET and POST
     */
    public function any(string $path, callable $handler, array $middleware = []): self
    {
        $this->addRoute('GET', $path, $handler, $middleware);
        $this->addRoute('POST', $path, $handler, $middleware);
        return $this;
    }

    /**
     * Add a route to the registry
     */
    private function addRoute(string $method, string $path, callable $handler, array $middleware): void
    {
        // Convert path parameters like {id} to regex
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method'     => $method,
            'path'       => $path,
            'pattern'    => $pattern,
            'handler'    => $handler,
            'middleware'  => $middleware,
        ];
    }

    /**
     * Dispatch the current request
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getRequestUri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware
                foreach ($route['middleware'] as $mw) {
                    if (is_callable($mw)) {
                        $result = call_user_func($mw);
                        if ($result === false) {
                            return; // Middleware blocked the request
                        }
                    }
                }

                // Call handler with positional arguments
                call_user_func_array($route['handler'], array_values($params));
                return;
            }
        }

        // No route matched → 404
        http_response_code(404);
        include TEMPLATES_PATH . '/errors/404.php';
    }

    /**
     * Get clean request URI without base path and query string
     */
    private function getRequestUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = rawurldecode($uri);

        // Normalize backslashes
        $uri = str_replace('\\', '/', $uri);

        // Strip base path (e.g. /portal-bip)
        if ($this->basePath !== '' && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }

        // Strip /index.php if present in URI
        if (strpos($uri, '/index.php') === 0) {
            $uri = substr($uri, 10);
        }

        $uri = '/' . trim($uri, '/');
        
        return $uri === '' ? '/' : $uri;
    }
}
