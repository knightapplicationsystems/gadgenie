<?php


$ref = $_GET['r'];
$usrAgent = $_SERVER['HTTP_USER_AGENT'];
$reqUrl = $_SERVER['REQUEST_URI'];
$ip = $_SERVER['REMOTE_ADDR'];
$td = date("Y-m-d H:i:s");
$token = $_GET['tkn'];
$cust = $_GET['c'];
$route = $_GET['rt'];

//Warranty Expiry Date
$exp = date('Y-m-d H:i:s', strtotime('+1 year'));
$pymnt = $_GET['p'];
$sPrice = $_GET['sp'];
$usr = $_GET['usr'];

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
        logRequest($link, $usrAgent, $td, $ip, $api,$reqUrl);
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
        update_stock($link,$ref, $usrAgent, $reqUrl, $ip, $td, $exp, $pymnt,$route,$cust,$sPrice,$usr);
    }
} catch (Exception $e) {
    echo json_encode($e);
}



function update_stock($link,$ref,$usrAgent,$reqUrl,$ip,$td,$exp,$pymnt,$route,$cust,$sPrice,$usr)
{
    
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
    
    $link->query("UPDATE _gg_stock
                    SET _pymnt_ref = '$pymnt',_warr_end = '$exp',_dt_sold = '$td',_sold_to = '$cust', _route = '$route',
                        _order_ref = '$unique_ref',_sp = $sPrice,_sold_by = '$usr',_picked=1,_dispatched=1,
                            _sold_to='Store Customer',_route=$route,_pick_by='$usr',_disp_by='$usr'
                    WHERE _code_id = '$ref'");
    

    log_sales_record($link, $unique_ref, $td, $ref, $sPrice, $usrAgent, $td, $ip, $reqUrl);
    
        $data = array();

        $data[] = array('itemSold' => "Item '$ref'");

        echo json_encode($data);
}

function log_sales_record($link,$unique_ref,$td,$ref,$sPrice,$usrAgent, $td, $ip, $reqUrl)
{
    $reqLogSQL = "INSERT INTO _gg_sales
                (_order_ref,_dt_sold,_item_ref,_sale_price)
                VALUES ('$unique_ref','$td', '$ref',$sPrice)";
    //echo $reqLogSQL;
    $link->query($reqLogSQL);
    

    
     $api = "ITEM SOLD - '$ref'";
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
