<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once("core/db_conn.php");

// JSON 데이터 받아오기
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['userid'], $data['password'])) {
    echo json_encode(["success" => false, "message" => "모든 필드를 입력하세요."],JSON_UNESCAPED_UNICODE);
    exit;
}

// 입력값 정리
$userid = trim($data['userid']);
$userpw = $data['password'];

try {
    // 사용자 조회
    $selectSql = "SELECT user_id, user_pw, username FROM user WHERE user_id = :userid";
    $stmt = $conn->prepare($selectSql);
    $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // 비밀번호 검증
        if (password_verify($userpw, $user['user_pw'])) {
            // 세션 저장
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            
            echo json_encode(["success" => true, "message" => "로그인 성공"],JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["success" => false, "message" => "비밀번호가 틀렸습니다."],JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode(["success" => false, "message" => "존재하지 않는 아이디입니다."],JSON_UNESCAPED_UNICODE);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()],JSON_UNESCAPED_UNICODE);
}
?>
