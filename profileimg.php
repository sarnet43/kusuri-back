<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once("core/db_conn.php");

// JSON 데이터 받아오기
$data = json_decode(file_get_contents("php://input"), true);

// 입력값 정리
$profileImg = $data['profileImg'];
$userid = $_SESSION['user_id'];

try {
    // 프로필 이미지 업데이트
    $updateSql = "UPDATE user SET profile_img = :profileImg WHERE user_id = :userid";
    $stmt = $conn->prepare($updateSql);
    $stmt->bindParam(":profileImg", $profileImg, PDO::PARAM_STR);
    $stmt->bindParam(":userid", $userid, PDO::PARAM_INT);

    // 실행 및 응답
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "프로필 이미지가 업데이트되었습니다."]);
    } else {
        echo json_encode(["success" => false, "message" => "프로필 이미지 업데이트 실패"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
