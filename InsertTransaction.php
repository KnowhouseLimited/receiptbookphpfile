<?php
require_once '../thereceiptbook/DbOperation.php';

$response = array();

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    if(
        isset($_POST['customer_full_name'])
        and isset($_POST['customers_phone_number']) and isset($_POST['purchased_item']) and
        isset($_POST['amount_paid'])and isset($_POST['receipt_issued_by'])){
        $db = new DbOperation();
        $results = $db->issueCustomerReceipt(
            $_POST['customer_full_name'],
            $_POST['customers_phone_number'],
            $_POST['purchased_item'],
            $_POST['amount_paid'],
            $_POST['receipt_issued_by']);
        if($results == 1){
            $response['error'] = false;
            $response['message'] = "Receipt issued";
        }elseif($results == 2){
            $response['error'] = true;
            $response['message'] = "Receipt not issued";
        }elseif ($results == 4){
            $response['error'] = true;
            $response['message'] = "Receipt not issued but insufficient bal for text";
        }elseif ($results == 5){
            $response['error'] = true;
            $response['message'] = "Receipt issued but invalid api key";
        }elseif ($results == 6){
            $response['error'] = true;
            $response['message'] = "Receipt issued but phone number not valid";
        }elseif ($results == 7){
            $response['error'] = true;
            $response['message'] = "Receipt issued but invalid sender id";
        }elseif ($results == 8){
            $response['error'] = true;
            $response['message'] = "Receipt issued but empty message";
        }elseif ($results == 9){
            $response['error'] = true;
            $response['message'] = "Receipt issued but unknown error occurred";
        }elseif ($results == 3){
            $response['error'] = true;
            $response['message'] = "Receipt issued but text not sent";
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
