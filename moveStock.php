<?php

$ref = $_GET['r'];

//Top level common params
$td = date("Y-m-d H:i:s");
//Security
$usrAgent = $_SERVER['HTTP_USER_AGENT'];
$ip = $_SERVER['REMOTE_ADDR'];
$token = $_GET['tkn'];


if (isset($_GET['d'])) {
    $dest = $_GET['d'];
} else {
    $dest = '';
}

if (isset($_GET['t'])) {
    $tran = $_GET['t'];
} else {
    $tran = '';
}


try {
    $link = include 'dbconfig.php';
} catch (mysqli_sql_exception $e) {
    echo json_encode($e);
}

try {
    //Check that the request comes with a valid token
    $res = $link->query("SELECT * FROM _gg_token WHERE _token = '$token'");
    $num_results = mysqli_num_rows($res);

    if ($num_results < 1) {
        $api = 'Invalid Token - /moveStock.php';
        logRequest($link, $usrAgent, $td, $ip, $api);
        $data = array();

        $data[] = array('resp' => "Token not recognised");

        echo json_encode($data);
        exit;
    }
    $row = mysqli_fetch_array($res, MYSQLI_NUM);

    if ($row[3] < $td) {
        $api = 'Token Expired - /moveStock.php';
        logRequest($link, $usrAgent, $td, $ip, $api);
        $data = array();

        $data[] = array('resp' => "Token Expired");

        echo json_encode($data);
        exit;
    } else {
        get_current_status($link,$ref,$dest, $usrAgent, $td, $ip);
    }
} catch (Exception $e) {
    echo json_encode($e);
}

function get_current_status($link,$ref,$dest, $usrAgent, $td, $ip)
{
    $resp = $link->query("SELECT * FROM _gg_stock WHERE _code_id = '$ref'");
    $row = mysqli_fetch_array($resp, MYSQLI_NUM);

    if ($row[17] == 0)
    {
        arrived_in_dest($link,$ref,$dest, $usrAgent, $td, $ip);
    }
    else if ($row[17] != $dest)
    {
        put_in_transit($link,$ref,$dest, $usrAgent, $td, $ip);
    }

    
}


function put_in_transit($link,$ref,$dest, $usrAgent, $td, $ip)
{
    $link->query("UPDATE _gg_stock SET _curr_loc = 0 WHERE _code_id = '$ref'");
        $api = "Item Put in Transit - Item was '$ref'";
        logRequest($link, $usrAgent, $td, $ip, $api);
        
     
    $row = mysqli_fetch_array($link->query("SELECT * FROM _gg_locations WHERE _id = '$dest'"), MYSQLI_NUM);
       $where = $row[1];
       $data = array();

        $data[] = array('resp' => "In Transit to '$where'");

        echo json_encode($data);
}

function arrived_in_dest($link,$ref,$dest, $usrAgent, $td, $ip)
{
   $link->query("UPDATE _gg_stock SET _curr_loc = $dest WHERE _code_id = '$ref'"); 
        $api = "Item Arrived - Item was '$ref'";
        logRequest($link, $usrAgent, $td, $ip, $api);
        $row = mysqli_fetch_array($link->query("SELECT * FROM _gg_locations WHERE _id = '$dest'"), MYSQLI_NUM);
        $where = $row[1];
        $data = array();

        $data[] = array('resp' => "Arrived at '$where'");

        echo json_encode($data);
}


function logRequest($link, $usrAgent, $td, $ip, $api) {
    $reqLogSQL = "INSERT INTO _gg_lg_access
                (_agent,_ip,_req_dt,_api)
                VALUES ('$usrAgent','$ip', '$td','$api')";

    $link->query($reqLogSQL);
}


?>
