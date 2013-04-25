<?php

$usrAgent = $_SERVER['HTTP_USER_AGENT'];
$reqUrl = $_SERVER['REQUEST_URI'];
$ip = $_SERVER['REMOTE_ADDR'];
$td = date("Y-m-d H:i:s");
$token = $_GET['tkn'];
$q = $_GET['q'];

if (isset($_GET['r'])) {
    $ref = $_GET['r'];
} else {
    $ref = '';
}

if (isset($_GET['usr'])) {
    $usr = $_GET['usr'];
} else {
    $usr = '';
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
        $api = 'Invalid Token - /getOrders.php';
        logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
        $data = array();

        $data[] = array('resp' => "Token not recognised");

        echo json_encode($data);
        exit;
    }
    $row = mysqli_fetch_array($res, MYSQLI_NUM);

    if ($row[3] < $td) {
        $api = 'Token Expired - /getOrders.php';
        logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
        $data = array();

        $data[] = array('resp' => "Token Expired");

        echo json_encode($data);
        exit;
    } else {

        if ($q == 'unpicked') {
            get_unpicked_orders($link, $usrAgent, $td, $ip, $reqUrl);
        } else if ($q == 'pick') {
            set_order_picked($ref, $link, $usrAgent, $td, $ip, $reqUrl,$usr);
        }
        else if ($q == 'disp'){
            mark_order_dispatched($ref, $link, $usrAgent, $td, $ip, $reqUrl,$usr);
        }
    }
} catch (Exception $e) {
    echo json_encode($e);
}

function get_unpicked_orders($link, $usrAgent, $td, $ip, $reqUrl) {

    $orders = $link->query("SELECT * FROM _gg_stock WHERE _picked = 0");
    $row = mysqli_fetch_array($orders, MYSQLI_NUM);
    $mem = $row[23];

    $data = array();
    try {
        $orders1 = $link->query("SELECT * FROM _gg_stock WHERE _picked = 0");
        while ($nRow = mysqli_fetch_array($orders1)) {
            $getDispAdd = $link->query("SELECT * FROM _gg_cust WHERE _memNumber = '$mem'");
            //echo "SELECT * FROM _gg_cust WHERE _memNumber = '$mem'";
            $row1 = mysqli_fetch_array($getDispAdd, MYSQLI_NUM);

            $add1 = $row1 [9];
            $add2 = $row1 [10];
            $town = $row1 [11];
            $pcode = $row1 [12];


            $data[] = array('memID' => $nRow["_sold_to"], 'stockName' => $nRow["_stock_name"],
                'stockRef' => $nRow["_code_id"], 'dateSold' => $nRow["_dt_sold"], 'dispAdd1' => $add1,
                'dispAdd2' => $add2, 'dispTown' => $town, 'dispPcode' => $pcode,
                'warrantyEnd' => $nRow["_warr_end"], 'itemLocation' => $nRow["_curr_loc"]);
        }

        echo json_encode($data);
    } catch (Exception $e) {
        echo json_encode($e);
    }

    $api = 'Get all unpicked orders';
    logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
}

function set_order_picked($ref, $link, $usrAgent, $td, $ip, $reqUrl,$usr) {

    $link->query("UPDATE _gg_stock
                    SET _picked = 1, _pick_by = '$usr'
                    WHERE _code_id = '$ref'");

    $data = array();

    $data[] = array('resp' => "Item Picked - '$ref'");

    echo json_encode($data);
    
    $api = ("Item $ref picked and ready for dispatch");
    
    logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);

}

function mark_order_dispatched($ref,$link, $usrAgent, $td, $ip, $reqUrl,$usr)
{
    $link->query("UPDATE _gg_stock
                    SET _dispatched = 1, _disp_by = '$usr'
                    WHERE _code_id = '$ref'");

    $data = array();

    $data[] = array('resp' => "Item Dispatched - '$ref'");

    echo json_encode($data);
    
    $api = ("Item $ref dispatched");
    
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
