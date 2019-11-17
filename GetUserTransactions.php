<?php
require_once '../thereceiptbook/DbOperation.php';

$products = array();
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $db = new DbOperation();
    $user_transactions = array();
    $user_transactions = $db->getUserTransactions($_POST['users_credential_id']);
}

echo json_encode($user_transactions);