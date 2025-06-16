<?php
$request = trim($_SERVER['REQUEST_URI'], '/');
$segments = explode('/', $request);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        if ($segments[1] === 'login') {
            require_once __DIR__ . '/../controllers/UserController.php';
            login($conn); // 함수 호출
        }
        else if ($segments[1] === 'join') {
            require_once __DIR__ . '/../controllers/UserController.php';
            joinus($conn);
        }
        else if ($segments[1] === 'check-id') {
            require_once __DIR__ . '/../controllers/UserController.php';
            checkId($conn);
        }
        else if ($segments[1] === 'logout') {
            require_once __DIR__ . '/../controllers/UserController.php';
            logout($conn);
        }

        break;
    case 'PATCH':
        if ($segments[1] === 'update') {
            require_once __DIR__ . '/../controllers/UserController.php';
            updateUserInfo($conn);
        }
        else if ($segments[1] === 'first-info') {
            require_once __DIR__ . '/../controllers/UserController.php';
            myinfo_1st_update($conn);
        }
        break;
    case 'GET':
        if ($segments[1] === 'user') {
            require_once __DIR__ . '/../controllers/UserController.php';
            getUser($conn);
        }
    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method Not Allowed']);
        break;
}
