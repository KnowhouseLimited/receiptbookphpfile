<?php

require_once dirname(__FILE__).'/DBConnect.php';
class DbOperation
{
    private $con;

    function __construct()
    {
        $db = new DBConnect();
        $this->con = $db->connect();
    }

    public function createUser($full_name,$company,$phone_number,$pass,$confirm_pass){
        if($this->isUserExist($phone_number)){
            return 0;
        }else {
            $password = md5($pass);
            $confirm_password = md5($confirm_pass);
            $query = 'call register_users(?,?,?,?,?)';
            //$q=$this->con->init();
            $stmt = $this->con->prepare($query);
            $stmt->bind_param('ssiss', $full_name, $company, $phone_number, $password, $confirm_password);

            if ($stmt->execute()) {
                return 1;
            } else {
                return 2;
            }
        }
    }

    public function userLogin($phone_number,$pass){
        $password = md5($pass);
        $stmt = $this->con->prepare("select id from users_credential where phone_number= ? and confirm_password = ?; ");
        $stmt->bind_param("is",$phone_number,$password);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;

    }

    public function getUserByPhoneNumber($phone_number){
        //DbOperation::$user_phone_number = $phone_number;
        $stmt = $this->con->prepare("select * from users_credential where phone_number=?");
        $stmt->bind_param("i",$phone_number);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }


    private function isUserExist($phone_number)
    {

        $stmt = $this->con->prepare("select id from users_credential where phone_number = ?");
        $stmt->bind_param("i",$phone_number);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }


    //Get the Transactions between the user and the customer
    public function getUserTransactions($id)
    {
        $stmt = $this->con->prepare("select id,customer_full_name, transactions,date
                                    from users_trasactions where users_credential_id = ? order by id desc ");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $stmt->bind_result($id,$fullName,$transactions,$date);
        $products = array();
        while($stmt->fetch()){
         $temp = array();
         $temp['id'] = $id;
         $temp['full_name'] = $fullName;
         $temp['transactions'] = $transactions;
         $temp['date'] = $date;
         array_push($products,$temp);
        }
        return $products;
    }

    public function issueCustomerReceipt($customer_name,$customer_phone_no,
                    $purchased_item,$amount_paid,$receipt_issued_by){

            $query = $this->con->prepare("select id,full_name from users_credential where phone_number = ? ");
            $query->bind_param("i",$receipt_issued_by);
            $query->execute();
            $query->bind_result($user_id,$users_name);
            while($query->fetch()){
                $users_full_name = $users_name;
                $users_identity = $user_id;
            }

            $transaction = "A receipt was issued to ".$customer_name." by ".$users_full_name." for the purchase of ".$purchased_item.
                " which cost an amount of GHS ".$amount_paid;
            $transaction = str_replace("\n","",$transaction);
            $stmt = $this->con->prepare("insert into users_trasactions
                                            (users_credential_id, customer_full_name, customers_phone_number,
                                             purchased_item, amount_paid, transactions)
                                            values (?,?,?,?,?,?)");
            $stmt->bind_param("isisis",$users_identity,$customer_name,$customer_phone_no,$purchased_item,
                $amount_paid,$transaction);

            //Send sms notification

            if($stmt->execute()){
                $smsResult = $this->sendMessage($customer_phone_no,$transaction);

                switch($smsResult){
                    case "1000":                //Message sent
                        return 1;
                        break;
                    case "1002":                //Message not sent
                        return 3;
                        break;
                    case "1003":                //You don't have enough balance
                        return 4;
                        break;
                    case "1004":                 //Invalid API Key
                        return 5;
                        break;
                    case "1005":                 //Phone number not valid
                        return 6;
                        break;
                    case "1006":                  //Invalid Sender ID
                        return 7;
                        break;
                    case "1008":                    //Empty message
                        return 8;
                        break;
                    default:                        //Unknown error message
                        return 9;
                }
            }else{
                return 2;
            }
    }

    public function sendMessage($phone_number,$sms_message){
        $key="8e53a72b3d651b34d987"; //your unique API key
        $message=urlencode(trim($sms_message,"\n")); //encode url
        $phone = urlencode(trim($phone_number,"\n"));
        $sender_id = "KnowHouse";

        /*******************API URL FOR SENDING MESSAGES********/
        $url="http://clientlogin.bulksmsgh.com/smsapi?key=$key&to=$phone&msg=$message&sender_id=$sender_id";


        /****************API URL TO CHECK BALANCE****************/
        //$url="http://clientlogin.bulksmsgh.com/api/smsapibalance?key=$key";


        $result=file_get_contents($url); //call url and store result;

        return $result;

    }
}