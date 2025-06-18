<?php
header('Content-Type: application/json; charset=utf-8');
require_once ("core/db_conn.php");
require_once ("core/medicine.php");

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
    if (!isset($_GET['med_id'])) {
        echo json_encode(["error" => "med_id not provided"]);
        exit;
    }
    $med_id = intval($_GET['med_id']);

    if (!isset($_SESSION['id'])) {
        echo json_encode(["error" => "User not logged in"]);
        exit;
    }
    $userid = $_SESSION['id'];
    
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
            $deleteSql = "DELETE FROM watchedmedicine WHERE user_id = :user_id AND med_id = :med_id";
            $stmt = $conn->prepare($deleteSql);
            $stmt->bindParam(":med_id", $med_id, PDO::PARAM_INT);
            $stmt->bindParam(":user_id", $userid, PDO::PARAM_INT);
            $stmt->execute();

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
    if (!isset($_SESSION['id'])) {
        echo json_encode(["error" => "User not logged in"]);
        exit;
    }
    $userid = $_SESSION['id'];

    $selectSql = "SELECT m.*
                    FROM watchedmedicine wm
                    JOIN medicine m ON wm.med_id = m.med_id
                    WHERE wm.user_id = :userid
                    ORDER BY id desc;";
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

    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['med_id'])) {
        echo json_encode(["error" => "med_id not provided"]);
        exit;
    }

    $med_id = intval($data['med_id']);

    // 약 존재 확인
    $medicine = new Medicine($conn);
    $medData = $medicine->getMedicineById($med_id);
    if (!$medData) {
        echo json_encode(["error" => "Medicine not found"]);
        exit;
    }

    // 찜 여부 확인
    $checkSql = "SELECT 1 FROM favoritemedicine WHERE med_id = :med_id AND user_id = :user_id";
    $stmt = $conn->prepare($checkSql);
    $stmt->bindParam(":med_id", $med_id, PDO::PARAM_INT);
    $stmt->bindParam(":user_id", $userid, PDO::PARAM_INT);
    $stmt->execute();
    $isFavorite = $stmt->fetch(PDO::FETCH_ASSOC);

    if ((bool)$isFavorite) {
        // 이미 찜된 경우 => 찜 취소
        $deleteSql = "DELETE FROM favoritemedicine WHERE med_id = :med_id AND user_id = :user_id";
        $delStmt = $conn->prepare($deleteSql);
        $delStmt->bindParam(":med_id", $med_id, PDO::PARAM_INT);
        $delStmt->bindParam(":user_id", $userid, PDO::PARAM_INT);
        if ($delStmt->execute()) {
            echo json_encode(["success" => true, "favorited" => false, "message" => "찜이 취소되었습니다."]);
        } else {
            echo json_encode(["error" => "찜 취소 실패"]);
        }
    } else {
        // 찜 안 된 경우 => 찜 추가
        $insertSql = "INSERT INTO favoritemedicine(med_id, user_id) VALUES (:med_id, :user_id)";
        $insStmt = $conn->prepare($insertSql);
        $insStmt->bindParam(":med_id", $med_id, PDO::PARAM_INT);
        $insStmt->bindParam(":user_id", $userid, PDO::PARAM_INT);
        if ($insStmt->execute()) {
            echo json_encode(["success" => true, "favorited" => true, "message" => "찜에 추가되었습니다."]);
        } else {
            echo json_encode(["error" => "찜 추가 실패"]);
        }
    }
}


//찜한 약 보기
function getFavorites($conn) {
    if (!isset($_SESSION['id'])) {
        echo json_encode(["error" => "User not logged in"]);
        exit;
    }

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

//찜 여부 확인
function isFavoriteMedicine($conn) {
    if (!isset($_SESSION['id'])) {
        echo json_encode(["error" => "User not logged in"]);
        exit;
    }

    $userid = $_SESSION['id'];

    $med_id = filter_input(INPUT_GET, 'med_id', FILTER_VALIDATE_INT);
    if ($med_id === false) {
        echo json_encode(["error" => "Invalid or missing med_id"]);
        exit;
    }

    $sql = "SELECT 1 FROM favoritemedicine WHERE med_id = :med_id AND user_id = :user_id";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':med_id', $med_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userid, PDO::PARAM_INT);
        $stmt->execute();

        $isFavorite = $stmt->fetchColumn();
        echo json_encode(["favorited" => (bool)$isFavorite]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error", "details" => $e->getMessage()]);
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
        $stmt->bindParam(":userid", $userid, PDO::PARAM_INT);
        $stmt->bindParam(":med_name", $med_name, PDO::PARAM_STR);
        $stmt->execute();

        // 약이 모두 추가된 후에 약 개수를 구해서 user 테이블의 taking_med 필드 업데이트
        $stmt = $conn->prepare("SELECT COUNT(*) FROM takemedicine WHERE user_id = :userid");
        $stmt->bindParam(":userid", $userid, PDO::PARAM_INT);
        $stmt->execute();
        $medCount = $stmt->fetchColumn();

        // user 테이블의 taking_med 필드 업데이트
        $stmt = $conn->prepare("UPDATE user SET taking_med = :med_count WHERE id = :userid");
        $stmt->bindParam(":userid", $userid, PDO::PARAM_INT);
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

function deleteTakeMedicine($conn) {
    $userid = $_SESSION['id'];
    $medicine = $data['medicine'];

    $deleteSql = "DELETE FROM takemedicine WHERE user_id = :userid and medicine = :medicine";
    $stmt = $conn->prepare($selectSql);
    $stmt->bindParam(":userid", $userid, PDO::PARAM_INT);
    $stmt->bindParam(":medicine", $medicine, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "복용중인 약이 삭제되었습니다."], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["success" => false, "message" => "복용중인 약 삭제를 실패했습니다" ], JSON_UNESCAPED_UNICODE);
    }
}


?>
