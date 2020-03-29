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
            $query = "insert into users_credential 
                        (full_name, company, phone_number, user_pass, confirm_password) 
                        values (?,?,?,?,?);";
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

            $query = $this->con->prepare("select id,full_name,company from users_credential where phone_number = ? ");
            $query->bind_param("i",$receipt_issued_by);
            $query->execute();
            $query->bind_result($user_id,$users_name,$users_company);
            while($query->fetch()){
                $users_full_name = $users_name;
                $users_identity = $user_id;
				$users_company_name = $users_company;
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
                $smsResult = $this->sendMessage($customer_phone_no,$transaction,$users_company_name);

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

    public function sendMessage($phone_number,$sms_message,$company){
        $key="8e53a72b3d651b34d987"; //your unique API key
        $message=urlencode(trim($sms_message,"\n")); //encode url
        $phone = urlencode(trim($phone_number,"\n"));
        $sender_id = urlencode(trim($company,"\n"));

        /*******************API URL FOR SENDING MESSAGES********/
        $url="http://clientlogin.bulksmsgh.com/smsapi?key=$key&to=$phone&msg=$message&sender_id=$sender_id";


        /****************API URL TO CHECK BALANCE****************/
        //$url="http://clientlogin.bulksmsgh.com/api/smsapibalance?key=$key";


        $result=file_get_contents($url); //call url and store result;

        return $result;

    }

    public function updateUserInfo($userid,$image,$image_string,$full_name,$phone_number,$company_name){

        $stmt = $this->con->prepare("UPDATE users_credential
                                                    set full_name = ?, 
                                                        phone_number = ?,
                                                        image_url = ?,
                                                        image_bitmap = ?,
                                                        company = ?
                                                    where id = ?");
        $stmt->bind_param("sisssi",$full_name,$phone_number,$image_string,$image,$company_name,$userid);
        if($stmt->execute()){
            return 1;
        }
        else
            return 0;
    }

    public function getDataValueForDataFeed($phone_number,$current_date){
        $query = $this->con->prepare("select id, company 
                                            from users_credential 
                                            where phone_number = ?");
        $query->bind_param("i",$phone_number);
        if($query->execute()){
            $query->bind_result($userid,$company);
            while ($query->fetch()){
                $user_id_no = $userid;
                $user_company_name = $company;
            }

            $smt = $this->con->prepare("select COUNT(purchased_item) as 
                                                count_of_items
                                                from users_trasactions
                                                where users_credential_id = ? and 
                                                      date = ?
                                                group by purchased_item");
            $smt->bind_param("is",$user_id_no,$current_date);
            if($smt->execute()){
                $product_count_sum = 0;
                $maximum = 0;
                $smt->bind_result($count_of_each_item);
                while ($smt->fetch()){
                    if($count_of_each_item > $maximum)
                        $maximum = $count_of_each_item;
                    $product_count_sum  += $count_of_each_item;
                }

            }

            $statement = $this->con->prepare("select SUM(amount_paid) as totalAmountEarned
                                                    from users_trasactions
                                                    where users_credential_id = ?
                                                    and date = ?");
            $statement->bind_param("is",$user_id_no,$current_date);
            $statement->bind_result($totalEarnedMoney);
            if($statement->execute()){
                while ($statement->fetch()){
                    $totalMoneyEarnedInADay = $totalEarnedMoney;
                }
            }

            $stmt = $this->con->prepare("select purchased_item 
                                                from users_trasactions
                                                where users_credential_id = ?
                                                and date = ?
                                                group by  purchased_item
                                                having count(purchased_item) = ?");
            $stmt->bind_param("isi",$user_id_no,$current_date,$maximum);
            $stmt->bind_result($most_purchase_item_of_day);
            $most_purchased="";
            if($stmt->execute()){
                while ($stmt->fetch()){
                    $most_purchased = $most_purchase_item_of_day;
                }

                $temp = array();
                $temp['issuerCompany'] = $user_company_name;
                $temp['noReceiptIssued'] = $product_count_sum;
                $temp['totalItemsSold'] = $product_count_sum;
                $temp['itemWithHighestReceipt'] = $most_purchased;
                $temp['totalPriceOfItemsSold'] = $totalMoneyEarnedInADay;
                $temp['error'] = false;
            }else{
                $temp['error'] = true;
            }
        }


        $dataValues = array();
        array_push($dataValues,$temp);
        return $dataValues;

    }

    public function getValueForGraph($user_phone,$date){
        $stmt = $this->con->prepare("select id
                                            from users_credential
                                            where phone_number = ?");

        $stmt->bind_param("i",$user_phone);
        $stmt->bind_result($userId);
        if($stmt->execute()){
            while($stmt->fetch()){
                $user_id = $userId;
            }

            $query = $this->con->prepare("select purchased_item, 
                                                    count(purchased_item) as barentry
                                                from users_trasactions
                                                where users_credential_id = ?
                                                        and date = ?
                                                group by purchased_item");
            $query->bind_param("is",$user_id,$date);

            $graphValues = array();

            if($query->execute()){
                $query->bind_result($item,$entries);
                while($query->fetch()){
                    $temp  = array();
                    $temp['item'] = $item;
                    $temp['entry'] = $entries;
                    $temp['error'] = false;
                    array_push($graphValues,$temp);
                }
                return $graphValues;
            }else{
                $temp = array();
                $temp['error'] = true;
            }
        }
        array_push($graphValues,$temp);
        return $graphValues;
    }
}