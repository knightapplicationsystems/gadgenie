<?php


$email = $_GET['em'];

$usrAgent = $_SERVER['HTTP_USER_AGENT'];
$reqUrl = $_SERVER['REQUEST_URI'];
$ip = $_SERVER['REMOTE_ADDR'];
$td = date("Y-m-d H:i:s");
$token = $_GET['tkn'];


try
    {  
        $link = include 'dbconfig.php';
    }
    catch (mysqli_sql_exception $e)
    {
        echo json_encode($e);
    }

try {
    //Check that the request comes with a valid token
    $res = $link->query("SELECT * FROM _gg_token WHERE _token = '$token'");
    $num_results = mysqli_num_rows($res);

    if ($num_results < 1) {
        $api = 'Invalid Token - /sellStock.php';
        logRequest($link, $usrAgent, $td, $ip, $api);
        $data = array();

        $data[] = array('resp' => "Token not recognised");

        
        echo json_encode($data);
        exit;
    }
    $row = mysqli_fetch_array($res, MYSQLI_NUM);

    if ($row[3] < $td) {
        $api = 'Token Expired - /sellStock.php';
        logRequest($link, $usrAgent, $td, $ip, $api);
        $data = array();

        $data[] = array('resp' => "Token Expired");

        echo json_encode($data);
        exit;
    } else {
        reset_pw($email, $link, $usrAgent, $td, $ip, $reqUrl);
    }
} catch (Exception $e) {
    echo json_encode($e);
}

function reset_pw($email,$link, $usrAgent, $td, $ip, $reqUrl)
{
    $unique_ref_length = 10;

        $possible_chars = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";

        $unique_ref = "";

        $i = 0;

        while ($i < $unique_ref_length) {

            // Pick a random character from the $possible_chars list  
            $char = substr($possible_chars, mt_rand(0, strlen($possible_chars) - 1), 1);

            $unique_ref .= $char;

            $i++;
        }
        
        $fst = $link->query("SELECT * FROM _gg_cust WHERE _email = '$email'");
        
        $row = mysqli_fetch_array($fst, MYSQLI_NUM);

        $salt = $row[19];
        
        $deErb = hash('sha1', "$salt$unique_ref");
        
        $link->query("UPDATE _gg_cust SET _pwd = '$deErb' WHERE _email = '$email'");
        
        require_once 'lib/stock_qr/emailer.php';
        
        $mailType = 'Your new password';
        
        send_password_reminder($email, $mailType, $unique_ref);
        
        $data = array();
 
        $data[] = array('newpassword' => $unique_ref);

        echo json_encode($data);
        
        $api = "Forgotton password reset for user $email";
        
        logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
        
    
}



//Audit Log
function logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl) {
    $reqLogSQL = "INSERT INTO _gg_lg_access
                (_agent,_ip,_req_dt,_api,_uri)
                VALUES ('$usrAgent','$ip', '$td','$api','$reqUrl')";

    $link->query($reqLogSQL);
}


?>
