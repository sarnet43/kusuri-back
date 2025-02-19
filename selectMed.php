<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
require_once ("core/db_conn.php");
require_once ("core/Medicine.php");

$userid = $_SESSION['user_id'];

$selectSql = "SELECT * FROM selectmed WHERE user_id = :userid ORDER BY id DESC";
$stmt = $conn->prepare($selectSql);
$stmt->bindParam(":userid", $userid, PDO::PARAM_STR);

if ($stmt->execute()) {
    $selectedMeds = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "message" => "불러오기 성공", "data" => $selectedMeds], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["success" => false, "message" => "불러오기 실패" ], JSON_UNESCAPED_UNICODE);
}



?>