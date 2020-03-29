<?php
require_once '../thereceiptbook/DbOperation.php';
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    if(isset($_POST['phone_number'])){
        $db = new DbOperation();
        $graphPieChartData = array();
        $graphPieChartData = $db->getValueForPieChart($_POST['phone_number']);
    }

    echo json_encode($graphPieChartData);
}