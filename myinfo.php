<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Method: POST");
header("Access-Control-Allow-Header: Content-Type");
include("./db_conn.php");

$data = json_decode(file_get_contents("php://input"), true);

$username = trim($data['username']);
$gender = $data['gender'];
$userid = $_SESSION['user_id'];

$updateSql = "update user set username = $username, gender = $gender where user_id = $userid;";

if(mysqli_query($conn, $updateSql)){
    echo json_encode(true);
}
else{
    echo json_encode(false);
}

?>