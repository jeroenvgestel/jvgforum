<?php

    class Router
    {
        private string $basePath;
        private array $routes = [];

        public function __construct()
        {
            $this->basePath = trim(BASEPATH, '/');
        }


        /**
         * Add a route to the router
         * @param string $method The request method, GET, POST, DELETE etc
         * @param string $route The route to take, with the requested parameters, for example: 'forums/view/{id:d}'
         * @param callable $callback The function to call when this route is requested
         * @return void
         */
        public function add(string $method, string $route, callable $callback): void
        {
            $route = trim($route, '/');

            $this->routes[] = [
                'method' => strtoupper($method),
                'route' => $route,
                'callback' => $callback
            ];

        }
        
        
        /**
         * Dispatch the request to the correct route
         * @param string $uri
         * @param string $requestMethod
         * @return mixed
         */
        public function dispatch(string $uri, string $requestMethod): mixed
        {

            $uri = trim(parse_url($uri, PHP_URL_PATH), '/');
            $requestMethod = strtoupper($requestMethod);

            if ($this->basePath && str_starts_with($uri, $this->basePath))
            {
                $uri = substr($uri, strlen($this->basePath) + 1);
            }            

            foreach ($this->routes as $route) 
            {
                if ($route['method'] !== $requestMethod)
                    continue;
 
                // Create the requested regex
                $pattern = preg_replace_callback('#\{([a-zA-Z0-9_]+):([a-z])}#', function($matches)
                {
                    $type = $matches[2]; 

                    // {parameter:d} for number
                    if ($type === 'd')
                        return '([0-9]+)';

                    // {parameter:s} for strings
                    elseif ($type === 's')
                        return '([a-zA-Z0-9_-]+)';
                    
                    // {just the parameter} for any
                    return '([a-zA-Z0-9_-]+)';

                }, $route['route']);
    
                if (preg_match("#^$pattern$#", $uri, $matches)) 
                {
                    array_shift($matches);
                    
                    return call_user_func_array($route['callback'], $matches);
                }
            }
    
            Response::Fail('Page not found', 404)->sendJSON();
            
            return false;
            
        }

    }