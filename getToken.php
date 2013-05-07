<?php

$api = '';
$key = $_GET["key"];
$usrAgent = $_SERVER['HTTP_USER_AGENT'];
$reqUrl = $_SERVER['REQUEST_URI'];
$ip = $_SERVER['REMOTE_ADDR'];
$td = date("Y-m-d H:i:s");

//Token Expiry 30 minutes
$expDate = date('Y-m-d H:i:s', strtotime('+30 minutes'));
try
{  
    $link = include 'dbconfig.php';
}
catch (mysqli_sql_exception $e)
{
    echo json_encode($e);
}



try {
    //$res = $link->query("SELECT * FROM _gg_api_users WHERE _key = '$key'");
    $res = $link->query("SELECT * FROM _gg_api_users WHERE _key = '$key'");
    $num_results = mysqli_num_rows($res); 
    if ($num_results < 1) {
        $api = 'Invalid key';
        logRequest($link,$usrAgent, $td, $ip, $api,$reqUrl);
        $data = array();
        
        $data[] = array('resp' => "Key not recognised");

        echo json_encode($data);
        exit;
    } else {
        $unique_ref_length = 25;

        $possible_chars = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

        $unique_ref = "";

        $i = 0;

        while ($i < $unique_ref_length) {

            // Pick a random character from the $possible_chars list  
            $char = substr($possible_chars, mt_rand(0, strlen($possible_chars) - 1), 1);

            $unique_ref .= $char;

            $i++;
        }

        $sql = "INSERT INTO _gg_token
                (_token,_createddt,_expires,_userAgent,_ip)
                VALUES ('$unique_ref','$td', '$expDate','$usrAgent','$ip')";

        $link->query($sql);
        $api = "Get Token for key $key";
        logRequest($link,$usrAgent, $td, $ip, $api,$reqUrl);

        $data = array();

        $data[] = array('token' => "$unique_ref");

        echo json_encode($data);
        
       
    }
} catch (Exception $e) {
    echo json_encode($e);
}



function logRequest($link,$usrAgent,$td,$ip,$api,$reqUrl)
{
    $reqLogSQL = "INSERT INTO _gg_lg_access
                (_agent,_ip,_req_dt,_api,_uri)
                VALUES ('$usrAgent','$ip', '$td','$api','$reqUrl')";
    
    $link->query($reqLogSQL);
}


$link->close();


?>
