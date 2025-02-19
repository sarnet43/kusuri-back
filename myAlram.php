<?php
session_start();
require_once("core/db_conn.php");

$userid = $_SESSION['userid'];

$selectSql = "SELECT * FROM alram WHERE user_id = :userid";
$stmt = $conn->prepare($selectSql);
$stmt->bindParam(":userid", $userid, PDO::PARAM_STR);

$myAlram = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($myAlram, JSON_UNESCAPED_UNICODE);


?>