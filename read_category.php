<?php
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
require_once ("core/db_conn.php");
require_once ("core/Medicine.php");

$medicine = new Medicine($conn);
$data = json_decode(file_get_contents("php://input"), true);

$category = $data['category'];
$stmt = $medicine->getMedicinesByCategory($category);
$categoryMedicines = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($categoryMedicines, JSON_UNESCAPED_UNICODE);

?>