<?php
require_once '../thereceiptbook/DbOperation.php';
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(
        isset($_POST['user_phone_no']) and isset($_POST['current_date'])){
        $db = new DbOperation();
        $result = array();
        $result = $db->getDataValueForDataFeed($_POST['user_phone_no'],
            $_POST['current_date']);
    }

    echo json_encode($result);
}