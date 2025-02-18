<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Method: POST");
header("Access-Control-Allow-Header: Content-Type");
include("./db_conn.php");

$data = json_decode(file_get_contents("php://input"), true);

if(!isset($data['userid'], $data['password'], $data['passwordcheck'])){
    echo "모든 필드 채우기";
}

//userid, password, passwordcheck
$userid = trim($data['userid']);
$passwd = password_hash($data['password'], PASSWORD_DEFAULT);

$checkSql = "select user_id from user;";
$id_result = mysqli_query($conn, $checkSql);
$cnt = mysqli_num_rows($id_result);

$insertSql = "insert into user(user_id, user_pw) values($userid, $passwd);";
if(mysqli_query($conn, $insertSql)){
    echo json_encode(true);
}
else{
    echo json_encode(false);
}



?>