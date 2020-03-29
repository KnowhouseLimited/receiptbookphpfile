<?php
require_once '../thereceiptbook/DbOperation.php';
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(isset($_POST['phone_number']) and isset($_POST['date'])){
        $db = new DbOperation();
        $graphData = array();
        $graphData = $db->getValueForGraph($_POST['phone_number'],$_POST['date']);
    }

    echo json_encode($graphData);
}