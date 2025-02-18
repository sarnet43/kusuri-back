<?php
header('Content-Type: application/json; charset=UTF-8');

require_once ("core/db_conn.php");
require_once ("core/Medicine.php");

$medicine = new Medicine($conn);

$med_id = 1; 

$medData = $medicine->getMedicineById($med_id);
echo json_encode($medData, JSON_UNESCAPED_UNICODE);

?>