<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once ("core/db_conn.php");
require_once ("core/Medicine.php");

//약 검색
function searchMedicine($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    $name = $data['name'] ?? ($_GET['name'] ?? null);

    if (!$name) {
        echo json_encode(["success" => false, "message" => "검색어(name)가 필요합니다."]);
        return;
    }

    $med_search = '%' . $name . '%';

    $selectSql = "SELECT * FROM medicine WHERE med_name_kr LIKE :search";
    $stmt = $conn->prepare($selectSql);
    $stmt->bindParam(":search", $med_search, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $search_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($search_result, JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["success" => false, "message" => "검색 실패"]);
    }
}


//카테고리별 약
function Medicine_cate($conn) {
    $medicine = new Medicine($conn);
    // GET 방식이면 $_GET['type'] 사용
    $category = isset($_GET['type']) ? $_GET['type'] : null;

    if (!$category) {
        echo json_encode(["error" => "카테고리(type) 파라미터가 필요합니다"]);
        return;
    }
    $stmt = $medicine->getMedicinesByCategory($category);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results, JSON_UNESCAPED_UNICODE);
}

//약 한 개만
function getOneMedicine($conn) {
    if (!isset($_GET['id'])) {
        echo json_encode(["error" => "med_id not provided"]);
        exit;
    }

    $med_id = intval($_GET['id']);
    $userid = $_SESSION['id'] ?? null;

    $medicine = new Medicine($conn);
    $medData = $medicine->getMedicineById($med_id);

    if (!$medData) {
        echo json_encode(["error" => "Medicine not found"]);
        exit;
    }

    try {
        $conn->beginTransaction();

        // 최근 본 약 저장
        if ($userid) {
            $insertSql = "INSERT INTO watchedmedicine(med_id, user_id)
                          VALUES (:med_id, :user_id)";
            $stmt = $conn->prepare($insertSql);
            $stmt->bindParam(":med_id", $med_id, PDO::PARAM_INT);
            $stmt->bindParam(":user_id", $userid, PDO::PARAM_INT);
            $stmt->execute();
        }

        // 조회수 증가
        $med_views = $medData['views'] + 1;
        $updateSql = "UPDATE medicine SET views = :med_views WHERE med_id = :med_id";
        $stmt2 = $conn->prepare($updateSql);
        $stmt2->bindParam(":med_views", $med_views, PDO::PARAM_INT);
        $stmt2->bindParam(":med_id", $med_id, PDO::PARAM_INT);
        $stmt2->execute();

        $conn->commit();

        echo json_encode($medData, JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(["error" => $e->getMessage()]);
    }
}


//봤던 약 불러오기
function watchedMedicine($conn) {
    $userid = $_SESSION['id'];

    $selectSql = "SELECT m.*
                    FROM watchedMedicine wm
                    JOIN medicine m ON wm.med_id = m.med_id
                    WHERE wm.user_id = :userid;";
    $stmt = $conn->prepare($selectSql);
    $stmt->bindParam(":userid", $userid, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $watchedMeds = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "message" => "불러오기 성공", "data" => $watchedMeds], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["success" => false, "message" => "불러오기 실패" ], JSON_UNESCAPED_UNICODE);
    }
}

//약 찜하기
function favoriteMedicine($conn) {
    $userid = $_SESSION['id'] ?? null;
    if (!$userid) {
        echo json_encode(["error" => "Not logged in"]);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['med_id'])) {
        echo json_encode(["error" => "med_id not provided"]);
        exit;
    }
    $med_id = intval($data['med_id']);

    $medicine = new Medicine($conn);
    $medData = $medicine->getMedicineById($med_id);
    if (!$medData) {
        echo json_encode(["error" => "Medicine not found"]);
        exit;
    }

    $insertSql = "INSERT INTO favoritemedicine(med_id, user_id)
                  VALUES (:med_id, :user_id)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bindParam(":med_id", $med_id, PDO::PARAM_INT);
    $stmt->bindParam(":user_id", $userid, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Failed to insert into selectedmed"]);
    }
}

//찜한 약 보기
function getFavorites($conn) {
    $userid = $_SESSION['id'];

    $selectSql = "SELECT m.*
                    FROM favoritemedicine fm
                    JOIN medicine m ON fm.med_id = m.med_id
                    WHERE fm.user_id = :userid;
                    ";
    $stmt = $conn->prepare($selectSql);
    $stmt->bindParam(":userid", $userid, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $selectedMeds = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "message" => "불러오기 성공", "data" => $selectedMeds], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["success" => false, "message" => "불러오기 실패" ], JSON_UNESCAPED_UNICODE);
    }
}

//약 랭킹 가져오기 
function getMedicineRank($conn) {
    $medicine = new Medicine($conn);
    $medicineRanking = $medicine->getMedicineAllDesc();
    echo json_encode($medicineRanking, JSON_UNESCAPED_UNICODE);
}

//복용중인 약 저장하기 
function takingMedicine($conn) {

    $userid = $_SESSION['id'];

    // JSON 데이터 받아오기
    $data = json_decode(file_get_contents("php://input"), true);

    // 약 이름이 없거나 비어 있으면 오류 반환
    if (!isset($data['med_name']) || empty($data['med_name'])) {
        echo json_encode(["success" => false, "message" => "Invalid medication data"]);
        exit;
    }

    $med_name = $data['med_name'];

    try {
        $conn->beginTransaction(); // 트랜잭션 시작

        // 약 삽입
        $stmt = $conn->prepare("INSERT INTO takemedicine (medicine, user_id) VALUES ( :med_name, :userid)");
        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        $stmt->bindParam(":med_name", $med_name, PDO::PARAM_STR);
        $stmt->execute();

        // 약이 모두 추가된 후에 약 개수를 구해서 user 테이블의 taking_med 필드 업데이트
        $stmt = $conn->prepare("SELECT COUNT(*) FROM takemedicine WHERE user_id = :userid");
        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        $medCount = $stmt->fetchColumn();

        // user 테이블의 taking_med 필드 업데이트
        $stmt = $conn->prepare("UPDATE user SET taking_med = :med_count WHERE id = :userid");
        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        $stmt->bindParam(":med_count", $medCount, PDO::PARAM_INT);
        $stmt->execute();

        $conn->commit(); // 트랜잭션 커밋

        echo json_encode(["success" => true, "message" => "Medication saved successfully", "current_count" => $medCount]);
    } catch (Exception $e) {
        $conn->rollBack(); // 오류 발생 시 롤백
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
}

function mytakeMedicine($conn) {
    $userid = $_SESSION['id'];

    $selectSql = "SELECT * FROM takemedicine WHERE user_id = :userid";
    $stmt = $conn->prepare($selectSql);
    $stmt->bindParam(":userid", $userid, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $selectedMeds = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "message" => "불러오기 성공", "data" => $selectedMeds], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["success" => false, "message" => "불러오기 실패" ], JSON_UNESCAPED_UNICODE);
    }
}


?>
