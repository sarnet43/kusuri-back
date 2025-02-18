<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once("core/db_conn.php");

// JSON 데이터 받아오기
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['userid'])) {
    echo json_encode(["success" => false, "message" => "아이디를 입력하세요."]);
    exit;
}

$userid = trim($data['userid']);

try {
    // 중복 확인 (같은 user_id 존재 여부 체크)
    $checkSql = "SELECT COUNT(*) FROM user WHERE user_id = :userid";
    $stmt = $conn->prepare($checkSql);
    $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
    $stmt->execute();
    
    $cnt = $stmt->fetchColumn();

    if ($cnt > 0) {
        echo json_encode(["success" => true, "exists" => true]); // 아이디 존재함
    } else {
        echo json_encode(["success" => true, "exists" => false]); // 사용 가능
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
