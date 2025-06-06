<?php

$request = trim($_SERVER['REQUEST_URI'], '/');
$segments = explode('/', $request);

$resource = $segments[1] ?? '';

$routesPath = __DIR__ . "/routes/{$resource}.php";

if (file_exists($routesPath)) {
    require $routesPath;
} else {
    http_response_code(404);
    echo json_encode(['message' => 'API route not found']);
}

?>
