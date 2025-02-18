<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Method: POST");
header("Access-Control-Allow-Header: Content-Type");
include("./db_conn.php");

$data = json_decode(file_get_contents("php://input"), true);

$selectSql = "select user_id from user;";
$id_result = mysqli_query($conn, $selectSql);
$cnt = mysqli_num_rows($id_result);

//중복 아이디 확인
for($i = 0; $i < $cnt; $i++){
    $re = mysqli_fetch_array($id_result);
    if($re['user_id'] == $userid){
        echo json_encode(false);
        exit();
    }
}
echo json_encode(true);