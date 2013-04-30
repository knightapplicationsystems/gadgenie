<?php

$usrAgent = $_SERVER['HTTP_USER_AGENT'];
$reqUrl = $_SERVER['REQUEST_URI'];
$ip = $_SERVER['REMOTE_ADDR'];
$td = date("Y-m-d H:i:s");

$token = $_GET['tkn'];
$username = $_GET['u'];
$password = $_GET['p'];

if (isset($_GET['t'])) {
    $t = $_GET['t'];
} else {
    $t = '';
}


try {
    $link = $mysqli = include 'dbconfig.php';
} catch (mysqli_sql_exception $e) {
    echo json_encode($e);
}


try {
    //Check that the request comes with a valid token
    $res = $link->query("SELECT * FROM _gg_token WHERE _token = '$token'");
    $num_results = mysqli_num_rows($res);

    if ($token == '') {
        $api = 'No Token - /login.php';
        logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
        $data = array();

        $data[] = array('resp' => "No Token");
        echo json_encode($data);
        exit;
    } else {
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

            if ($t == 'staff') {
                
                $password = hash('md5', "$password");
                $query = $link->query("SELECT * FROM _gg_staff WHERE _code = '$username' AND _pwd = '$password'");
                $num_results = mysqli_num_rows($query);

                if ($num_results < 1) {
                    $data = array();

                    $data[] = array('resp' => "Login invalid");

                    echo json_encode($data);
                    exit;
                } else {
                    $verify = $link->query("SELECT * FROM _gg_staff WHERE _code = '$username' AND _pwd = '$password'");

                    $data = array();

                    while ($nRow = mysqli_fetch_array($verify)) {
                        $data[] = array('code' => $nRow["_code"], 'fname' => $nRow["_fname"], 'sname' => $nRow["_sname"],
                            'admin' => $nRow["_is_admin"], 'sales' => $nRow["_is_sales"], 'backO' => $nRow["_is_back_office"]);

                        //echo 'Airport Name:' . $row["airport_name"];
                    }

                    echo json_encode($data);
                }
            } else {
                //Get Salt and Password
                $fst = $link->query("SELECT * FROM _gg_cust WHERE _uname = '$username'");

                $row = mysqli_fetch_array($fst, MYSQLI_NUM);

                $salt = $row[15];

                $deErb = hash('sha1', "$salt$password");
                //echo $deErb ;
                $verify = $link->query("SELECT * FROM _gg_cust WHERE _uname = '$username' AND _pwd = '$deErb'");
                //echo ("SELECT * FROM _gg_cust WHERE _uname = '$username' AND _pwd = '$deErb'");

                $data = array();
                try {

                    while ($nRow = mysqli_fetch_array($verify)) {
                        $data[] = array('memID' => $nRow["_memNumber"], 'fname' => $nRow["_fname"], 'sname' => $nRow["_sname"], 'dob' => $nRow["_dob"], 'add1' => $nRow["_add1"],
                            'add2' => $nRow["_add2"], 'town' => $nRow["_town"], 'pcode' => $nRow["_pcode"], 'hphone' => $nRow["_hphone"], 'mphone' => $nRow["_mphone"]
                            , 'email' => $nRow["_email"], 'country' => $nRow["_country"], 'county' => $nRow["_county"]);

                        //echo 'Airport Name:' . $row["airport_name"];
                    }

                    echo json_encode($data);
                } catch (Exception $e) {
                    echo json_encode($e);
                }
            }
        }
    }
} catch (Exception $e) {
    echo json_encode($e);
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
