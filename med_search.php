<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once("core/db_conn.php");

$data = json_decode(file_get_contents("php://input"), true);
$med_search = '%'.$data['search'].'%';

$selectSql = "SELECT * FROM medicine WHERE med_name_kr LIKE :search";
$stmt = $conn->prepare($selectSql);
$stmt->bindParam(":search", $med_search, PDO::PARAM_STR);

if($stmt->execute()){
    $search_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($search_result, JSON_UNESCAPED_UNICODE);
}
else{
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>