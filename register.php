<?php

$user = 'root';
$pass = '522561jh';

//Get some variables from the URL
$token = $_GET['tkn'];
$usrAgent = $_SERVER['HTTP_USER_AGENT'];
$reqUrl = $_SERVER['REQUEST_URI'];
$ip = $_SERVER['REMOTE_ADDR'];
$fname = $_GET['f'];
$sname = $_GET['s'];
$dob = $_GET['d'];
$add1 = $_GET['a1'];
$add2 = $_GET['a2'];
$city = $_GET['ct'];
$pcode = $_GET['pc'];
$county = $_GET['cy'];
$idt = $_GET['idt'];
$idd = $_GET['idd'];
$uname = $_GET['un'];
$pwd = $_GET['pw'];
$email = $_GET['e'];
$hphone = $_GET['hp'];
$mphone = $_GET['mp'];
$td = date("Y-m-d H:i:s");

try {
    $link = new mysqli("localhost", $user, $pass, "_gg");
} catch (mysqli_sql_exception $e) {
    echo json_encode($e);
}

if ($token == '') {
    $api = 'No Token - /register.php';
    logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
    $data = array();

    $data[] = array('resp' => "No Token");
    echo json_encode($data);
    exit;
} else {


    try {
        //Check that the request comes with a valid token
        $res = $link->query("SELECT * FROM _gg_token WHERE _token = '$token'");
        $num_results = mysqli_num_rows($res);

        if ($num_results < 1) {
            $api = 'Invalid Token - /register.php';
            logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
            $data = array();

            $data[] = array('resp' => "Token not recognised");

            echo json_encode($data);
            exit;
        }
        $row = mysqli_fetch_array($res, MYSQLI_NUM);

        if ($row[3] < $td) {
            $api = 'Token Expired - /register.php';
            logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
            $data = array();

            $data[] = array('resp' => "Token Expired");

            echo json_encode($data);
            exit;
        } else {
            //Salt
            $unique_ref_length = 64;
            $possible_chars = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

            $salt = "";

            $i = 0;
            while ($i < $unique_ref_length) {

                // Pick a random character from the $possible_chars list  
                $char = substr($possible_chars, mt_rand(0, strlen($possible_chars) - 1), 1);

                $salt .= $char;

                $i++;
            }

            //MemID
            $unique1_ref_length_id = 10;
            $possible_chars1 = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $unique_ref = "";

            $c = 0;

            while ($c < $unique1_ref_length_id) {

                // Pick a random character from the $possible_chars list  
                $char1 = substr($possible_chars1, mt_rand(0, strlen($possible_chars1) - 1), 1);

                $unique_ref .= $char1;

                $c++;
            }
            $memID = $unique_ref;
            $saltPass = "$salt$pwd";
            

            $saltedPass = hash('sha1', $saltPass);
            
            $sql = "INSERT INTO _gg_cust
                    (_memNumber,_fname,_sname,_dob,_add1,_add2,_town,_pcode,_hphone,_mphone,_email,
                    _country,_county,_uname,_salt,_pwd,_id_type,_id_detail)
                    VALUES
                    ('$memID','$fname','$sname','$dob','$add1','$add2','$city','$pcode','$hphone',
                     '$mphone','$email','UK', '$county','$uname','$salt','$saltedPass','$idt','$idd')";

            $link->query($sql);

            $data = array();

            $data[] = array('memID' => "$memID");

            echo json_encode($data);

            $api = '/register.php';

            logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
        }
    } catch (Exception $e) {
        
    }
}

//Audit Log
function logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl) {
    $reqLogSQL = "INSERT INTO _gg_lg_access
                (_agent,_ip,_req_dt,_api,_uri)
                VALUES ('$usrAgent','$ip', '$td','$api','$reqUrl')";

    $link->query($reqLogSQL);
}

$link->close();
?>

