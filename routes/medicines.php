<?php
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // 쿼리 제거
$request = trim($requestUri, '/');
$segments = explode('/', $request);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if (isset($segments[2]) && $segments[2] === 'search') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            searchMedicine($conn);

        } elseif ($segments[2] === 'category') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            Medicine_cate($conn);

        } elseif ($segments[2] === 'medicine') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            getOneMedicine($conn);
        } elseif ($segments[2] === 'ranking') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            getMedicineRank($conn);
        } elseif ($segments[2] === 'my-favorite-medicine') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            getFavorites($conn);
        } elseif ($segments[2] === 'watched-medicine') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            watchedMedicine($conn);
        } elseif ($segments[2] === 'my-take-medicine') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            mytakeMedicine($conn);
        }

        break;

    case 'POST':
        if ($segments[2] === 'favorite') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            favoriteMedicine($conn);
        } elseif ($segments[2] === 'taking-medicine') {
            require_once __DIR__. '/../controllers/MedicineController.php';
            takingMedicine($conn);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method Not Allowed']);
        break;
}


?>