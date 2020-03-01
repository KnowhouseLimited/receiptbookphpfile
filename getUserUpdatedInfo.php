<?php
require_once '../thereceiptbook/DbOperation.php';
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(isset($_POST['userid']) and isset($_POST['full_name']) and isset($_POST['phone_number'])
        and isset($_POST['image_url']) and isset($_POST['image']) and isset($_POST['company'])){
        $db = new DbOperation();
        $result = $db->updateUserInfo(
            $_POST['userid'],
            $_POST['image'],
            $_POST['image_url'],
            $_POST['full_name'],
            $_POST['phone_number'],
            $_POST['company']);

        if($result == 1){
            $response = array('error'=>false,'message'=>"Save Successful");
            echo json_encode($response);
        }else{
            $response = array('error'=>true,'message'=>"Save Unsuccessful");
            echo json_encode($response);
        }
    }
}

