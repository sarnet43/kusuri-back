<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
require_once ("core/db_conn.php");
require_once ("core/Medicine.php");

$data = json_decode(file_get_contents("php://input"), true);
$userid = $_SESSION['user_id'];

if (isset($data['med_id'])) {
    $med_id = $data['med_id'];
} else {
    echo json_encode(["error" => "med_id not provided"]);
    exit;
}

try{
    $conn->beginTransaction();

    $medicine = new Medicine($conn);
    $medData = $medicine->getMedicineById($med_id);

    $med_name_kr = $medData['med_name_kr'];
    $med_name_jp = $medData['med_name_jp']; 
    $med_explanation = $medData['med_explanation'];
    $med_views = $medData['views'];

    //특정 약 데이터
    echo json_encode($medData, JSON_UNESCAPED_UNICODE);
    
    //최근 본 약 저장
    $insertSql = "INSERT INTO watchedmed(med_id, med_name_kr, med_name_jp, med_explanation, user_id) VALUES (:med_id, :med_name_kr, :med_name_jp, :med_explanation, :user_id)";
    $stmt = $conn->prepare($insertSql);
    $stmt = bindParam(":med_id", $med_id, PDO::PARAM_INT);
    $stmt = bindParam(":med_name_kr", $med_name_kr, PDO::PARAM_STR);
    $stmt = bindParam(":med_name_jp", $med_name_jp, PDO::PARAM_STR);
    $stmt = bindParam(":med_explanation", $med_explanation, PDO::PARAM_STR);
    $stmt = bindParam(":user_id", $userid, PDO::PARAM_STR);

    // 쿼리 실행
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Failed to insert into watchedmed"]);
    }

    //조회수 업데이트
    $med_views = $med_views + 1;
    $updateSql = "UPDATE medicine SET views = :med_views WHERE med_id = :med_id";
    $result = $conn->prepare($updateSql);
    $result->bindParam(":med_views", $med_views, PDO::PARAM_INT);
    if ($result->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "조회수 업데이트 실패"]);
    }

    $pdo->commit(); // 트랜잭션 커밋
} catch(Exception $e) {
    $pdo->rollBack(); // 오류 발생 시 롤백
    echo json_encode(["error" => $e->getMessage()]);
}


?>