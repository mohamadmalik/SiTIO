<?php declare(strict_types=1);

class Router {
    protected array $routes = [];

    public function get(string $path, callable $handler) {
        if(!isset($this->routes["GET"])) $this->routes["GET"] = [];
        $this->routes["GET"][$path] = $handler;
    }

    public function post(string $path, callable $handler) {
        if(!isset($this->routes["POST"])) $this->routes["POST"] = [];
        $this->routes["POST"][$path] = $handler;
    }

    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI']);
        $base = trim(dirname($_SERVER['SCRIPT_NAME']));
        $absPath = rtrim($uri['path'], "/");
        $path = "/" . trim(substr($absPath, strlen($base)), "/");

        define("BASE_URL", $base);

        // Find match url
        foreach($this->routes[$method] as $route => $handler) {
            if($route == $path) return $handler(new Request());
            $routePattern = preg_replace("/\//", "\\/", $route);
            $routePattern = preg_replace("/:\w+/", "(.+)", $routePattern);

            preg_match("/^" . $routePattern . "$/", $route, $routeMatches); // get needed cols
            if(preg_match("/^" . $routePattern . "$/", $path, $matches)) {
                $_SERVER["URL_PARAM"] = [];
                for($i = 1; $i < count($matches); $i++) {
                    $field = substr($routeMatches[$i], 1);
                    $_SERVER["URL_PARAM"][$field] = $matches[$i];
                }

                return $handler(new Request());
            }
        }

        return http_response_code(404);
    }
}
