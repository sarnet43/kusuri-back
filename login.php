<?php
session_start();
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

$selectSql = "select user_id, user_pw, username from user where user_id = '$userid';";
$result = mysqli_query($conn, $selectSql);
$cnt = mysqli_num_rows($result);

if($cnt == 1){
    $re = mysqli_fetch_array($result);
    if(password_verify($userpw, $re['user_pw'])){
        //비밀번호 맞았을 시 세션 저장, true 반환
        $_SESSION['user_id'] = $re['user_id'];
        $_SESSION['username'] = $re['username'];
        echo json_encode(true);
    }
    else{ //비밀번호 틀렸을 시 false 반환
        echo json_encode(false);
    }
}

?>