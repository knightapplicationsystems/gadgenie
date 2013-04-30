<?php

$ref = $_GET['r'];
$usrAgent = $_SERVER['HTTP_USER_AGENT'];
$reqUrl = $_SERVER['REQUEST_URI'];
$ip = $_SERVER['REMOTE_ADDR'];
$td = date("Y-m-d H:i:s");
$token = $_GET['tkn'];
$oPrice = $_GET['op'];
$ePrice = $_GET['ep'];
$remarks = $_GET['re'];
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
        update_stock($ref,$oPrice,$ePrice, $usr, $remarks, $link, $usrAgent, $td, $ip, $api,$reqUrl);
    }
} catch (Exception $e) {
    echo json_encode($e);
}


function update_stock($ref,$oPrice,$ePrice, $usr, $remarks,$link,$usrAgent,$td,$ip,$api,$reqUrl)
{
    $link->query("UPDATE _gg_stock
                    SET _sp = $ePrice
                    WHERE _code_id = '$ref'");
    
    echo "UPDATE _gg_stock
                    SET _sp = $ePrice
                    WHERE _code_id = '$ref'";
    
    
    $data = array();

        $data[] = array('price_updated' => "Item '$ref'");

        echo json_encode($data);
        audit_log($ref, $oPrice, $ePrice, $usr, $remarks, $link, $usrAgent, $td, $ip, $api, $reqUrl);
}

function audit_log($ref,$oPrice,$ePrice,$usr,$remarks,$link, $usrAgent, $td, $ip, $api, $reqUrl)
{
    
    $link->query("INSERT INTO _gg_over_log
                    (_code_id,_o_price,_n_price,_date,_usr,_remarks)
                    VALUES ('$ref',$oPrice,$ePrice,'$td','$usr','$remarks')");
    
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
