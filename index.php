<?php
session_set_cookie_params([
    'lifetime' => 43200, 
    'secure' => true,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'None'
]);
session_start();

error_log('현재 경로: ' . $_SERVER['REQUEST_URI']);
error_log('받은 쿠키: ' . print_r($_COOKIE, true));
error_log('세션 ID: ' . session_id());

$origin = "https://kusuri-green.vercel.app";
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, FATCH, DELETE, OPTIONS");

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
