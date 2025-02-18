<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once("core/db_conn.php");

// JSON 데이터 받아오기
$data = json_decode(file_get_contents("php://input"), true);

// 필수 필드 확인
if (!isset($data['userid'], $data['password'])) {
    echo json_encode(["success" => false, "message" => "모든 필드를 입력하세요."]);
    exit;
}

// 입력값 정리
$userid = trim($data['userid']);
$password = $data['password'];

// 비밀번호 해싱
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    // 사용자 정보 삽입
    $insertSql = "INSERT INTO user (user_id, user_pw) VALUES (:userid, :password)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
    $stmt->bindParam(":password", $hashedPassword, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "회원가입 성공"]);
    } else {
        echo json_encode(["success" => false, "message" => "회원가입 실패"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
