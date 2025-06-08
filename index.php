<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
