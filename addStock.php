<?php

$user = 'root';
$pass = '522561jh';

//Top level common params
$td = date("Y-m-d H:i:s");
//Security
$usrAgent = $_SERVER['HTTP_USER_AGENT'];
$ip = $_SERVER['REMOTE_ADDR'];
//Get some - Params from the inbound URL REQ
$sName = $_GET['sn'];
$sDesc = $_GET['sd'];
$cat = $_GET['ct'];
$scat = $_GET['sct'];
$pic = $_GET['pc'];
$upc = $_GET['upc'];
$cnd = $_GET['cn'];
$col = $_GET['co'];
$percentileCBP = $_GET['cbp'];
$percentileEBP = $_GET['ebp'];
$token = $_GET['tkn'];


try {
    $link = new mysqli("localhost", $user, $pass, "_gg");
} catch (mysqli_sql_exception $e) {
    echo json_encode($e);
}

try {
    //Check that the request comes with a valid token
    $res = $link->query("SELECT * FROM _gg_token WHERE _token = '$token'");
    $num_results = mysqli_num_rows($res);

    if ($num_results < 1) {
        $api = 'Invalid Token - /addStock.php';
        logRequest($link, $usrAgent, $td, $ip, $api);
        $data = array();

        $data[] = array('resp' => "Token not recognised");

        echo json_encode($data);
        exit;
    }
    $row = mysqli_fetch_array($res, MYSQLI_NUM);

    if ($row[3] < $td) {
        $api = 'Token Expired - /addStock.php';
        logRequest($link, $usrAgent, $td, $ip, $api);
        $data = array();

        $data[] = array('resp' => "Token Expired");

        echo json_encode($data);
        exit;
    } else {

        $unique_ref_length = 25;

        $possible_chars = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";

        $unique_ref = "";

        $i = 0;

        while ($i < $unique_ref_length) {

            // Pick a random character from the $possible_chars list  
            $char = substr($possible_chars, mt_rand(0, strlen($possible_chars) - 1), 1);

            $unique_ref .= $char;

            $i++;
        }

        try {
            getPrice($upc);
            
        } catch (Exception $e) {
            echo json_encode($e);
        }

        
          $sql = "INSERT INTO _gg_stock
          (_code_id,_stock_name,_stock_desc,_cat,_sub_cat,_pic,_barcode,_cond,_col,dt_add,_cbp,_ebp)
          VALUES ('$unique_ref','$sName', '$sDesc','$cat','$scat','$pic','$upc','$cnd','$col','$td',$percentileCBP,$percentileEBP)";

          $link->query($sql);
          $api = '/addStock.php';
          logRequest($link, $usrAgent, $td, $ip, $api);

          $data = array();

          $data[] = array('code' => "$unique_ref");

          echo json_encode($data);
          
         
    }
} catch (Exception $e) {
    echo json_encode($e);
}


function logRequest($link, $usrAgent, $td, $ip, $api) {
    $reqLogSQL = "INSERT INTO _gg_lg_access
                (_agent,_ip,_req_dt,_api)
                VALUES ('$usrAgent','$ip', '$td','$api')";

    $link->query($reqLogSQL);
}

$link->close();
?>

