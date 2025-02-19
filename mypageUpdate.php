<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

require_once("core/db_conn.php");
$data = json_decode(file_get_contents("php://input"), true);

//id, username, password, gender, profile_img
$id = $_SESSION['id'];
$username = $data['username'];
$password = $data['password'];
$gender = $data['gender'];
$profile_img = $data['profile_img'];

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$updateSql = "UPDATE user SET user_pw = :Password, username = :username, gender = :gender, profile_img = :profile_img WHERE id = :id";
$stmt = $conn->prepare($updateSql);
$stmt->bindParam(":Password", $hashedPassword, PDO::PARAM_STR);
$stmt->bindParam(":username", $username, PDO::PARAM_STR);
$stmt->bindParam(":gender", $gender, PDO::PARAM_STR);
$stmt->bindParam(":profile_img", $profile_img, PDO::PARAM_STR);
$stmt->bindParam(":id", $id, PDO::PARAM_INT);

if($stmt->execute()){
    echo json_encode(["success" => true, "message" => "프로필 수정 성공."],JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["success" => false, "message" => "프로필 수정 실패."],JSON_UNESCAPED_UNICODE);
}

?>