<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once("core/db_conn.php");

//로그인  
function login($conn) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['userid'], $data['password'])) {
        echo json_encode(["success" => false, "message" => "모든 필드를 입력하세요."],JSON_UNESCAPED_UNICODE);
        exit;
    }

    $userid = trim($data['userid']);
    $userpw = $data['password'];

    try {
        // 사용자 조회
        $selectSql = "SELECT id, user_id, user_pw, username, gender, profile_img FROM user WHERE user_id = :userid";
        $stmt = $conn->prepare($selectSql);
        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 비밀번호 검증
            if (password_verify($userpw, $user['user_pw'])) {
                // 세션 저장
                $_SESSION['id'] = $user['id'];
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                
                echo json_encode([
                    "success" => true,
                    "message" => "로그인 성공",
                    "username" => $user['username'],
                    "gender" => $user['gender'],     
                    "profile" => $user['profile_img']     
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(["success" => false, "message" => "비밀번호가 틀렸습니다."],JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode(["success" => false, "message" => "존재하지 않는 아이디입니다."],JSON_UNESCAPED_UNICODE);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()],JSON_UNESCAPED_UNICODE);
    }
}

//회원가입 
function joinus($conn) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['userid'], $data['password'])) {
            echo json_encode(["success" => false, "message" => "모든 필드를 입력하세요."],JSON_UNESCAPED_UNICODE);
            exit;
        }

        $userid = trim($data['userid']);
        $password = $data['password'];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM `user` WHERE user_id = :userid");
        $checkStmt->bindParam(":userid", $userid);
        $checkStmt->execute();

        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode(["success" => false, "message" => "이미 존재하는 아이디입니다."], JSON_UNESCAPED_UNICODE);
            return;
        }

        $insertSql = "INSERT INTO `user` (user_id, user_pw) VALUES (:userid, :password)";
        $stmt = $conn->prepare($insertSql);
        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        $stmt->bindParam(":password", $hashedPassword, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "회원가입 성공"],JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["success" => false, "message" => "회원가입 실패"],JSON_UNESCAPED_UNICODE);
        }

    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()],JSON_UNESCAPED_UNICODE);
    }
}


//중복 아이디 체크
function checkId($conn) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['userid'])) {
        echo json_encode(["success" => false, "message" => "아이디를 입력하세요."]);
        exit;
    }

    $userid = trim($data['userid']);

    try {
        // 중복 확인 (같은 user_id 존재 여부 체크)
        $checkSql = "SELECT COUNT(*) FROM user WHERE user_id = :userid";
        $stmt = $conn->prepare($checkSql);
        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        $stmt->execute();
        
        $cnt = $stmt->fetchColumn();

        if ($cnt > 0) {
            echo json_encode(["success" => true, "exists" => true]); // 아이디 존재함
        } else {
            echo json_encode(["success" => true, "exists" => false]); // 사용 가능
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
}

//첫 로그인시 정보 업데이트 
function myinfo_1st_update($conn) {
    session_start();
    
    if (!isset($_SESSION['id'])) {
        echo json_encode(["success" => false, "message" => "세션이 만료되었거나 로그인되지 않았습니다."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    $username = trim($data['username']);
    $gender = $data['gender'];
    $profile_img = $data['profile'];
    $userid = $_SESSION['id'];
    $userid = $_SESSION['id'];

    try {
        // 사용자 정보 업데이트
        $updateSql = "UPDATE user SET username = :username, gender = :gender, profile_img = :profile_img WHERE id = :userid";
        $stmt = $conn->prepare($updateSql);
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->bindParam(":gender", $gender, PDO::PARAM_STR);
        $stmt->bindParam(":profile_img", $profile_img, PDO::PARAM_STR);
        $stmt->bindParam(":profile_img", $profile_img, PDO::PARAM_STR);
        $stmt->bindParam(":userid", $userid, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "프로필 설정 성공."], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["success" => false, "message" => "프로필 설정 실패."], JSON_UNESCAPED_UNICODE);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

//로그아웃 
function logout() {
    $_SESSION = array();

    // 세션 ID 쿠키 삭제
    if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
    }

    // 세션 파일 삭제
    session_destroy();
    echo json_encode(["success" => true, "message" => "로그아웃 되었습니다."], JSON_UNESCAPED_UNICODE);
}

//유저 정보 수정 
function updateUserInfo($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    
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

    
}
?>