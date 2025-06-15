<?php
//require_once __DIR__ . '/../vendor/autoload.php'; 

// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
// $dotenv->load();

$host = getenv('DB_HOST');
$database = getenv('DB_NAME');
$id = getenv('DB_USER');
$pw = getenv('DB_PASS');

try {
    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";
    $conn = new PDO($dsn, $id, $pw);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
} catch(PDOException $e) {
    echo "DB 연결 실패: " . $e->getMessage();
}
?>
