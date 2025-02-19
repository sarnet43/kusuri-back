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

    // 특정 카테고리별 의약품 가져오기
    public function getMedicinesByCategory($category) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE 
                  :category IN (cate_1, cate_2, cate_3, cate_4, cate_5)";
        $stmt = $this->conn->prepare($query);
      
        $stmt->execute([':category' => $category]); // 카테고리 파라미터 하나만 전달
        return $stmt;
    }
    
}
?>
