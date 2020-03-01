<?php
require_once '../thereceiptbook/DbOperation.php';

$response = array();

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(isset($_POST['phone_number']) and isset($_POST['confirm_password'])) {
        $db = new DbOperation();
        if ($db->userLogin($_POST['phone_number'], $_POST['confirm_password'])) {
            $user = $db->getUserByPhoneNumber($_POST['phone_number']);
            $response['error'] = false;
            $response['id'] = $user['id'];
            $response['phone_number'] = $user['phone_number'];
            $response['company'] = $user['company'];
            $response['full_name'] = $user['full_name'];
            $response['image'] = $user['image_bitmap'];
            $response['image_url'] = $user['image_url'];
			$response['message'] = "Login Successful";
        } else {
            $response['error'] = true;
            $response['message'] = "Invalid phone number or password";
        }
    }else{
        $response['error'] = true;
        $response['message'] = "Require fields are missing";
    }
}
echo json_encode($response);