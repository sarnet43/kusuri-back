<?php
class Medicine {
    private $conn;
    private $table_name = "medicine"; // 테이블 이름 설정

    // 생성자: DB 연결
    public function __construct($db) {
        $this->conn = $db;
    }

    public function getMedicineAllDesc(){
        $query = "SELECT * FROM ".$this->table_name . " ORDER BY views DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 특정 ID에 해당하는 의약품 가져오기
    public function getMedicineById($med_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE med_id = :med_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":med_id", $med_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMedicinesByCategory($category) {
    $query = "SELECT * FROM " . $this->table_name . " WHERE 
          cate_1 = :cat1 OR cate_2 = :cat2 OR cate_3 = :cat3 
          OR cate_4 = :cat4 OR cate_5 = :cat5";

    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(':cat1', $category, PDO::PARAM_STR);
    $stmt->bindValue(':cat2', $category, PDO::PARAM_STR);
    $stmt->bindValue(':cat3', $category, PDO::PARAM_STR);
    $stmt->bindValue(':cat4', $category, PDO::PARAM_STR);
    $stmt->bindValue(':cat5', $category, PDO::PARAM_STR);

    $stmt->execute();
    return $stmt;
    }
}
?>
