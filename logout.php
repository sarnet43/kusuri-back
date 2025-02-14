<?php
if (!session_id()) {
  session_start();
}

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
?>