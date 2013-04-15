<?php

$user = 'root';
$pass = '522561jh';

//Top level common params
$td = date("Y-m-d H:i:s");
//Security
$usrAgent = $_SERVER['HTTP_USER_AGENT'];
$ip = $_SERVER['REMOTE_ADDR'];
$upc = $_GET['upc'];
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
        getPrice($upc,$link,$usrAgent,$td,$ip);
    }
} catch (Exception $e) {
    echo json_encode($e);
}

function getPrice($upc,$link,$usrAgent,$td,$ip) {
    $uri = "http://www.searchupc.com/handlers/upcsearch.ashx?request_type=3&access_token=44ED474B-CA18-4B27-B147-D2C1DBF41C60&upc=$upc";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uri);

    ob_start();
    curl_exec($ch);
    $json1 = ob_get_clean();
    $json = json_decode(utf8_encode($json1), true);

    //var_dump ($json);

    $desc = $json[0]['productname'];
    //echo $desc;
    $price = $json[0]['price'];
    //$calcPriceMath1 = round($price * 0.653299,2);
    //$calcPriceMath2 = $price - $calcPriceMath1;
    //echo round($calcPriceMath1,2);

    $percentileCBP = round($price * 0.60, 2);
    $percentileEBP = round($price * 0.75, 2);

    $data = array();

    $data[] = array('desc' => "$desc", 'rrp' => "$price", 'exchangePrice' => "$percentileEBP", 'cashPrice' => "$percentileCBP");
    $api = "/Search for '$upc'.php";
    logRequest($link, $usrAgent, $td, $ip, $api);
    echo json_encode($data);
    

}

function logRequest($link, $usrAgent, $td, $ip, $api) {
    $reqLogSQL = "INSERT INTO _gg_lg_access
                (_agent,_ip,_req_dt,_api)
                VALUES ('$usrAgent','$ip', '$td','$api')";

    $link->query($reqLogSQL);
}

?>
