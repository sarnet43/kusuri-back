<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

require_once("core/db_conn.php");

// JSON 데이터 받아오기
$data = json_decode(file_get_contents("php://input"), true);

// 입력값 정리
$username = trim($data['username']);
$gender = $data['gender'];
$userid = $_SESSION['user_id'];

try {
    // 사용자 정보 업데이트
    $updateSql = "UPDATE user SET username = :username, gender = :gender WHERE user_id = :userid";
    $stmt = $conn->prepare($updateSql);
    $stmt->bindParam(":username", $username, PDO::PARAM_STR);
    $stmt->bindParam(":gender", $gender, PDO::PARAM_STR);
    $stmt->bindParam(":userid", $userid, PDO::PARAM_INT);

    // 실행 및 응답
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "프로필 설정 성공."],JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["success" => false, "message" => "프로필 설정 실패."],JSON_UNESCAPED_UNICODE);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()],JSON_UNESCAPED_UNICODE);
}
?>
