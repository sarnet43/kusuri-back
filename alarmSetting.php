<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once("core/db_conn.php");

$userid = $_SESSION['user_id']; 
// JSON 데이터 받아오기
$data = json_decode(file_get_contents("php://input"), true);

$medicine = $data['medicine'];
$time = $data['time'];
$timeslot = $data['timeslot'];
$day_type = $data['day_type'];
$start_day = $data['start_day'];
$last_day = $data['last_day'];
$state = 'On';

if($day_type == '매일'){
    $days = "Mon,Tue,Wed,Thu,Fri,Sat,Sun";
} elseif($day_type == '격일'){
    if ($start_day) {
        $start_date = strtotime($start_day);
        $days_array = [];
        for ($i = 0; $i < 7; $i++) {
            $day_name = date("D", strtotime("+$i days", $start_date));
            if ($i % 2 === 0) { // 격일 계산
                $days_array[] = substr($day_name, 0, 3);
            }
        }
        $days = implode(",", $days_array);
    }
}
else{
    $days = $day_type; //'Mon', 'Tue'...
}

$insertSql = "INSERT INTO alarm (user_id, medicine, time, timeslot, days, start_day, last_day, state) 
              VALUES (:userid, :medicine, :time, :timeslot, :days, :start_day, :last_day, :state)";
$stmt = $conn->prepare($insertSql);
$stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
$stmt->bindParam(':medicine', $medicine, PDO::PARAM_STR);
$stmt->bindParam(':time', $time, PDO::PARAM_STR);
$stmt->bindParam(':timeslot', $timeslot, PDO::PARAM_STR);
$stmt->bindParam(':days', $days, PDO::PARAM_STR);
$stmt->bindParam(':start_day', $start_day,PDO::PARAM_STR);
$stmt->bindParam(':last_day', $last_day,PDO::PARAM_STR);
$stmt->bindParam(':state', $state,PDO::PARAM_STR);

if ($stmt->execute()) {
    echo json_encode(["message" => "Alarm saved successfully"]);
} else {
    echo json_encode(["message" => "Error saving alarm"]);
}

?>