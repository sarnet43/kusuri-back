<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Method: POST");
header("Access-Control-Allow-Header: Content-Type");
include("./db_conn.php");

$data = json_decode(file_get_contents("php://input"), true);

if(!isset($data['userid'], $data['password'])){
    echo "모든 필드 채우기";
}
$userid = $data['userid'];
$userpw = $data['password'];

$selectSql = "select user_id, user_pw from user where user_id = '$userid';";
$result = mysqli_query($conn, $selectSql);
$cnt = mysqli_num_rows($result);

if($cnt == 1){
    $re = mysqli_fetch_array($result);
    if(password_verify($userpw, $re['user_pw'])){
        
    }
    else{
        echo "비밀번호가 틀렸습니다.";
    }
}

?>