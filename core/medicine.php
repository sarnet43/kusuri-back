<?php
class Medicine {
    private $conn;
    private $table_name = "medicine"; // 테이블 이름 설정

    // 생성자: DB 연결
    public function __construct($db) {
        $this->conn = $db;
    }

    // 특정 ID에 해당하는 의약품 가져오기
    public function getMedicineById($med_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE med_id = :med_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":med_id", $med_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 특정 카테고리별 의약품 가져오기
    public function getMedicinesByCategory($category) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE 
                  cate_1 = :category OR 
                  cate_2 = :category OR 
                  cate_3 = :category OR 
                  cate_4 = :category OR 
                  cate_5 = :category";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":category", $category, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt;
    }
}
?>
