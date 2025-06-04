<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once("core/db_conn.php");

// 알람 세팅
function alarmSetting($conn) {
    $userid = $_SESSION['id']; 
    $data = json_decode(file_get_contents("php://input"), true);

    $medicine = $data['medicine'];
    $time = $data['time'];
    $timeslot = $data['timeslot'];
    $day_type = $data['day_type'];
    $start_day = $data['start_day'];
    $last_day = $data['last_day'];
    $state = 'On';

    if ($day_type == '매일') {
        $days = "Mon,Tue,Wed,Thu,Fri,Sat,Sun";
    } elseif ($day_type == '격일') {
        if ($start_day) {
            $start_date = strtotime($start_day);
            $days_array = [];
            for ($i = 0; $i < 7; $i++) {
                $day_name = date("D", strtotime("+$i days", $start_date));
                if ($i % 2 === 0) {
                    $days_array[] = substr($day_name, 0, 3);
                }
            }
            $days = implode(",", $days_array);
        }
    } else {
        $days = $day_type; // 예: "Mon,Tue"
    }

    $insertSql = "INSERT INTO alarm (user_id, medicine, time, timeslot, days, start_day, last_day, state) 
                  VALUES (:userid, :medicine, :time, :timeslot, :days, :start_day, :last_day, :state)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
    $stmt->bindParam(':medicine', $medicine, PDO::PARAM_STR);
    $stmt->bindParam(':time', $time, PDO::PARAM_STR);
    $stmt->bindParam(':timeslot', $timeslot, PDO::PARAM_STR);
    $stmt->bindParam(':days', $days, PDO::PARAM_STR);
    $stmt->bindParam(':start_day', $start_day, PDO::PARAM_STR);
    $stmt->bindParam(':last_day', $last_day, PDO::PARAM_STR);
    $stmt->bindParam(':state', $state, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Alarm saved successfully"]);
    } else {
        echo json_encode(["message" => "Error saving alarm"]);
    }
}

// 알람 업데이트
function alarmUpdate($conn) {
    $userid = $_SESSION['id'];
    $data = json_decode(file_get_contents("php://input"), true);

    $medicine = $data['medicine'];
    $time = $data['time'];
    $timeslot = $data['timeslot'];
    $day_type = $data['day_type'];
    $start_day = $data['start_day'];
    $last_day = $data['last_day'];
    $state = $data['state'];

    if ($day_type == '매일') {
        $days = "Mon,Tue,Wed,Thu,Fri,Sat,Sun";
    } elseif ($day_type == '격일') {
        if ($start_day) {
            $start_date = strtotime($start_day);
            $days_array = [];
            for ($i = 0; $i < 7; $i++) {
                $day_name = date("D", strtotime("+$i days", $start_date));
                if ($i % 2 === 0) {
                    $days_array[] = substr($day_name, 0, 3);
                }
            }
            $days = implode(",", $days_array);
        }
    } else {
        $days = $day_type;
    }

    $updateSql = "UPDATE alarm SET medicine = :medicine, time = :time, timeslot = :timeslot, days = :days, start_day = :start_day, last_day = :last_day, state = :state
                  WHERE user_id = :userid";
    $stmt = $conn->prepare($updateSql);
    $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
    $stmt->bindParam(':medicine', $medicine, PDO::PARAM_STR);
    $stmt->bindParam(':time', $time, PDO::PARAM_STR);
    $stmt->bindParam(':timeslot', $timeslot, PDO::PARAM_STR);
    $stmt->bindParam(':days', $days, PDO::PARAM_STR);
    $stmt->bindParam(':start_day', $start_day, PDO::PARAM_STR);
    $stmt->bindParam(':last_day', $last_day, PDO::PARAM_STR);
    $stmt->bindParam(':state', $state, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Alarm updated successfully"]);
    } else {
        echo json_encode(["message" => "Error updating alarm"]);
    }
}

// 알람 삭제
function alarmDelete($conn) {
    $userid = $_SESSION['id'];
    $data = json_decode(file_get_contents("php://input"), true);

    $delete_type = $data['delete_type'];
    $med_id = $data['med_id'];

    if ($delete_type == 'all') {
        $deleteSql = "DELETE FROM alarm WHERE user_id = :userid";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "전체 삭제 성공"], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["success" => false, "message" => "삭제 실패"], JSON_UNESCAPED_UNICODE);
        }
    } else {
        $deleteSql = "DELETE FROM alarm WHERE id = :med_id";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bindParam(":med_id", $med_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "삭제 성공"], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["success" => false, "message" => "삭제 실패"], JSON_UNESCAPED_UNICODE);
        }
    }
}

// 알람 가져오기
function getAlarm($conn) {
    $userid = $_SESSION['id'];

    $selectSql = "SELECT * FROM alarm WHERE user_id = :userid";
    $stmt = $conn->prepare($selectSql);
    $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
    $stmt->execute();

    $myAlarm = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($myAlarm, JSON_UNESCAPED_UNICODE);
}
?>
