<?php

require_once '../thereceiptbook/DbOperation.php';

$response = array();

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    if(
        isset($_POST['full_name'])and isset($_POST['company'])
        and isset($_POST['phone_number']) and isset($_POST['user_pass']) and
            isset($_POST['confirm_password'])){
        $db = new DbOperation();
        $results = $db->createUser(
            $_POST['full_name'],
            $_POST['company'],
            $_POST['phone_number'],
            $_POST['user_pass'],
            $_POST['confirm_password']);
        if($results == 1){
         $response['error'] = false;
         $response['message'] = "User registered Successfully";
        }elseif($results == 2){
            $response['error'] = true;
            $response['message'] = "User registration failed";
        }elseif($results == 0){
            $response['error'] = true;
            $response['message'] = "Already registered";
        }

    }else{
        $response['error'] = true;
        $response['message'] = "Required fields are missing";
    }

}else{
    $response['error'] = true;
    $response['message'] = "Invalid Request";
}

echo json_encode($response);