<?php
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // 쿼리 제거
$request = trim($requestUri, '/');
$segments = explode('/', $request);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if (isset($segments[1]) && $segments[1] === 'search') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            searchMedicine($conn);

        } elseif ($segments[1] === 'category') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            Medicine_cate($conn);

        } elseif ($segments[1] === 'medicine') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            getOneMedicine($conn);
        } elseif ($segments[1] === 'ranking') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            getMedicineRank($conn);
        } elseif ($segments[1] === 'my-favorite-medicine') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            getFavorites($conn);
        } elseif ($segments[1] === 'watched-medicine') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            watchedMedicine($conn);
        } elseif ($segments[1] === 'my-take-medicine') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            mytakeMedicine($conn);
        } elseif ($segments[1] === 'is-favorite-medicine') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            isFavoriteMedicine($conn);
        }

        break;

    case 'POST':
        if ($segments[1] === 'favorite') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            favoriteMedicine($conn);
        } elseif ($segments[1] === 'taking-medicine') {
            require_once __DIR__. '/../controllers/MedicineController.php';
            takingMedicine($conn);
        }
        break;
        
    case 'DELETE':
        if ($segments[1] === 'take-medicine-delete') {
            require_once __DIR__ . '/../controllers/MedicineController.php';
            deleteTakeMedicine($conn);
        }
    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method Not Allowed']);
        break;
}
?>