<?php
require_once("core/db_conn.php");
require_once("core/medicine.php");

$medicine = new Medicine($conn);

$medicineRanking = $medicine->getMedicineAllDesc();

echo json_encode($medicineRanking, JSON_UNESCAPED_UNICODE);

?>  