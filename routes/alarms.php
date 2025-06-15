<?php
$request = trim($_SERVER['REQUEST_URI'], '/');
$segments = explode('/', $request);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        if ($segments[1] === 'alarm-setting') {
            require_once __DIR__ . '/../controllers/AlarmController.php';
            alarmSetting($conn);
        }
        break;
    case 'GET':
        if ($segments[1] === 'alarm') {
            require_once __DIR__ . '/../controllers/AlarmController.php';
            getAlarm($conn);
        }
        break;
    case 'PATCH':
        if($segments[1] === 'alarm-update') {
            require_once __DIR__. '/../controllers/AlarmController.php';
            alarmUpdate($conn);
        }
        break;
    case 'DELETE':
        if($segments[1] === 'alarm-delete') {
            require_once __DIR__. '/../controllers/AlarmController.php';
            alarmDelete($conn);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method Not Allowed']);
        break;
}
