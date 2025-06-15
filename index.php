<?php
$origin = "http://localhost:3000";
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$request = trim($_SERVER['REQUEST_URI'], '/');
$segments = explode('/', $request);

$resource = $segments[0] ?? '';

$routesPath = __DIR__ . "/routes/{$resource}.php";

if (file_exists($routesPath)) {
    require $routesPath;
} else {
    http_response_code(404);
    echo json_encode(['message' => 'API route not found']);
}

?>
