<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once("core/db_conn.php");
$userid = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input", true));
$delete_type = $data['delete_type'];
$med_id = $data['med_id'];

if($delete_type == 'all'){
    $deleteSql = "DELETE FROM alram WHERE user_id = :userid";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
    if($stmt->excute()){
        echo json_encode(["success" => true, "message" => "삭제 성공"],JSON_UNESCAPED_UNICODE);
    } else{
        echo json_encode(["success" => false, "message" => "삭제 실패"],JSON_UNESCAPED_UNICODE);
    }
} else{
    $deleteSql = "DELETE FROM alram WHERE id = :med_id";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bindParam(":med_id", $med_id, PDO::PARAM_INT);
    if($stmt->excute()){
        echo json_encode(["success" => true, "message" => "삭제 성공"],JSON_UNESCAPED_UNICODE);
    } else{
        echo json_encode(["success" => false, "message" => "삭제 실패"],JSON_UNESCAPED_UNICODE);
    }
}

?>