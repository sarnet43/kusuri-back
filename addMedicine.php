<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
require_once ("core/db_conn.php");
require_once ("core/Medicine.php");

$userid = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$med_id = $data['med_id'];

$medicine = new Medicine($conn);
$medData = $medicine->getMedicineById($med_id);
$med_name_kr = $medData['med_name_kr'];
$med_name_jp = $medData['med_name_jp']; 
$med_explanation = $medData['med_explanation'];

$insertSql = "INSERT INTO selectedmed(med_id, med_name_kr, med_name_jp, med_explanation, user_id) VALUES (:med_id, :med_name_kr, :med_name_jp, :med_explanation, :user_id)";
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
    echo json_encode(["error" => "Failed to insert into selectedmed"]);
}


?>