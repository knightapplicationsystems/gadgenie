<?php

$ref = $_GET['r'];

//Top level common params
$td = date("Y-m-d H:i:s");
//Security
$usrAgent = $_SERVER['HTTP_USER_AGENT'];
$ip = $_SERVER['REMOTE_ADDR'];
$token = $_GET['tkn'];

if (isset($_GET['usr'])) {
    $usr = $_GET['usr'];
} else {
    $usr = '';
}


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
        get_current_status($link,$ref,$dest, $usrAgent, $td, $ip,$usr);
    }
} catch (Exception $e) {
    echo json_encode($e);
}

function get_current_status($link,$ref,$dest, $usrAgent, $td, $ip,$usr)
{
    $resp = $link->query("SELECT * FROM _gg_stock WHERE _code_id = '$ref'");
    $row = mysqli_fetch_array($resp, MYSQLI_NUM);

    if ($row[17] == 0)
    {
        arrived_in_dest($link,$ref,$dest, $usrAgent, $td, $ip,$usr);
    }
    else if ($row[17] != $dest)
    {
        $curr_loc = $row[17];
        put_in_transit($link,$ref,$dest,$curr_loc, $usrAgent, $td, $ip,$usr);
    }

    
}


function put_in_transit($link,$ref,$dest,$curr_loc, $usrAgent, $td, $ip,$usr)
{
    new_tran_log($link,$ref,$dest,$curr_loc,$td,$usr);
    $link->query("UPDATE _gg_stock SET _curr_loc = 0 WHERE _code_id = '$ref'");
        $api = "Item Put in Transit - Item was '$ref'";
        logRequest($link, $usrAgent, $td, $ip, $api);
        
     
    $row = mysqli_fetch_array($link->query("SELECT * FROM _gg_locations WHERE _id = '$dest'"), MYSQLI_NUM);
       $where = $row[1];
       
       $data = array();

        $data[] = array('resp' => "In Transit to '$where'");

        echo json_encode($data);
      
        
        
}

function arrived_in_dest($link,$ref,$dest, $usrAgent, $td, $ip,$usr)
{
    update_tran_log($link, $ref, $td,$usr);
   $link->query("UPDATE _gg_stock SET _curr_loc = $dest WHERE _code_id = '$ref'"); 
        $api = "Item Arrived - Item was '$ref'";
        logRequest($link, $usrAgent, $td, $ip, $api);
        $row = mysqli_fetch_array($link->query("SELECT * FROM _gg_locations WHERE _id = '$dest'"), MYSQLI_NUM);
        $where = $row[1];
        $data = array();

        $data[] = array('resp' => "Arrived at '$where'");

        echo json_encode($data);
        
        
        
        
        //$link->close();
}

function update_tran_log($link,$ref,$td,$usr)
{
        $link->query("UPDATE _gg_tran_log
                  SET _date_in = '$td', usr = '$usr' WHERE _date_out != '$td' AND _item_ref= '$ref'");

        
    //$link->close();
}

function new_tran_log($link,$ref,$dest,$curr_loc,$td,$usr)
{
    $exp = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    $link->query("INSERT INTO _gg_tran_log
                  (_item_ref,_origin,_dest,_date_out,_warn_time,_usr)
                  VALUES
                  ('$ref',$curr_loc,$dest,'$td','$exp','$usr')");
    

    //$link->close();
}


function logRequest($link, $usrAgent, $td, $ip, $api) {
    $reqLogSQL = "INSERT INTO _gg_lg_access
                (_agent,_ip,_req_dt,_api)
                VALUES ('$usrAgent','$ip', '$td','$api')";

    $link->query($reqLogSQL);
    //$link->close();
}


?>
