<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once("core/db_conn.php");

$userid = $_SESSION['user_id'];

// JSON 데이터 받아오기
$data = json_decode(file_get_contents("php://input"), true);

// 약 이름이 없거나 비어 있으면 오류 반환
if (!isset($data['med_name']) || empty($data['med_name'])) {
    echo json_encode(["success" => false, "message" => "Invalid medication data"]);
    exit;
}

$med_name = $data['med_name'];

try {
    $conn->beginTransaction(); // 트랜잭션 시작

    // 약 삽입
    $stmt = $pdo->prepare("INSERT INTO alarm (user_id, medicine) VALUES (:userid, :mad_name)");
    $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
    $stmt->bindParam(":med_name", $med_name, PDO::PARAM_STR);
    $stmt->execute();

    // 약이 모두 추가된 후에 약 개수를 구해서 user 테이블의 taking_med 필드 업데이트
    $stmt = $conn->prepare("SELECT COUNT(*) FROM alarm WHERE user_id = :userid");
    $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
    $medCount = $stmt->fetchColumn();

    // user 테이블의 taking_med 필드 업데이트
    $stmt = $conn->prepare("UPDATE user SET taking_med = :med_count WHERE user_id = :userid");
    $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
    $stmt->bindParam(":med_count", $medCount, PDO::PARAM_INT);
    $stmt->execute();

    $conn->commit(); // 트랜잭션 커밋

    echo json_encode(["success" => true, "message" => "Medication saved successfully", "current_count" => $medCount]);
} catch (Exception $e) {
    $conn->rollBack(); // 오류 발생 시 롤백
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}

?>
