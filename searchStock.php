<?php

//Top level common params
$td = date("Y-m-d H:i:s");
//Security
$usrAgent = $_SERVER['HTTP_USER_AGENT'];
$ip = $_SERVER['REMOTE_ADDR'];

if (isset($_GET['upc'])) {
    $upc = $_GET['upc'];
} else {
    $upc = '';
}
if (isset($_GET['q'])) {
    $q = $_GET['q'];
} else {
    $q = '';
}

if (isset($_GET['t'])) {
    $t = $_GET['t'];
} else {
    $t = '';
}


$token = $_GET['tkn'];


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
        checkExistingStock($upc, $q, $t, $link, $usrAgent, $td, $ip);
    }
} catch (Exception $e) {
    echo json_encode($e);
}

function checkExistingStock($upc, $q, $t, $link, $usrAgent, $td, $ip) {

    if ($upc == '') {
        getPriceNoUPC($q, $t, $link, $usrAgent, $td, $ip);
    } else {

        $checkSQL = $link->query("SELECT * FROM _gg_stock_master WHERE _upc = '$upc'");
        $row = mysqli_fetch_array($checkSQL, MYSQLI_NUM);

        if ($row[3] == '') {
            getPrice($upc, $link, $usrAgent, $td, $ip);
        } else {
            $data = array();
            $checkSQL1 = $link->query("SELECT * FROM _gg_stock_master WHERE _upc = '$upc'");
            while ($nrow = mysqli_fetch_array($checkSQL1)) {
                $data[] = array('stock_name' => $nrow["_stock_name"], 'stock_desc' => $nrow["_stock_desc"], 'upc' => $nrow["_upc"],
                    'rrp' => $nrow["_rrp"], 'cash price' => $nrow["_cbp"], 'exchange price' => $nrow["_ebp"]);
            }
            echo json_encode($data);
            $api = "/Search for '$upc'.php";
            logRequest($link, $usrAgent, $td, $ip, $api);
            echo json_encode($data);
        }
    }
}

function getPriceNoUPC($q, $t, $link, $usrAgent, $td, $ip) {

    include("lib/amazon_api_class.php");

    $obj = new AmazonProductAPI();

    try {
        //$result = $obj->searchProducts("iPhone 5 Black 32GB", AmazonProductAPI::ELECTRONICS, "TITLE");
        //$result = $obj->searchProducts("885909636907", AmazonProductAPI::ELECTRONICS, "UPC");

        if ($t == 'ELECTRONICS') {
            $result = $obj->searchProducts("$q", AmazonProductAPI::ELECTRONICS, "TITLE");
        }
        if ($t == 'SOFTWARE') {
            $result = $obj->searchProducts("$q", AmazonProductAPI::SOFTWARE, "TITLE");
        }
        if ($t == 'COMPUTER') {
            $result = $obj->searchProducts("$q", AmazonProductAPI::COMPUTER, "TITLE");
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }

    //var_dump($result);

    $xml = new DOMDocument();
    $xml->loadXML($result->saveXML());

    $cUPC = '';
    $cTitle = '';
    $cRRP = '';
    foreach ($xml->getElementsByTagName('Item') as $node) {

        //echo $node->
        // From the item we can get child nodes
        foreach ($node->getElementsByTagName('UPC') as $upc) {

            $cUPC = ($upc->nodeValue);

        }
        foreach ($node->getElementsByTagName('Title') as $title) {

            //echo 'Title: ' . $title->nodeValue;
           // echo '<br>';
            
            $cTitle = ($title->nodeValue);
        }
        //LowestUsedPrice
        foreach ($node->getElementsByTagName('LowestNewPrice') as $lowPrice) {

            //echo 'LowestNewPrice: ' . $lowPrice->nodeValue;
            //echo '<br>';
            $cRRP = ($lowPrice->nodeValue);
            
        }


        // Doing it this way means we get get nodes specific to the item we are on (this is more controlled)  
        
        $percentileCBP = round($cRRP * 0.60, 2);

        $percentileEBP = round($cRRP * 0.75, 2);
        
        
            $data = array();

        $data[] = array('upc' => "$cUPC",'title' => "$cTitle", 'rrp' => "$cRRP", 'exchangePrice' => "$percentileEBP", 'cashPrice' => "$percentileCBP");
        echo json_encode($data);
    }
    
    

    
    
    
    
    /*
    $desc = $json[0]['productname'];

    $price = $json[0]['price'];
    
    $percentileCBP = round($price * 0.60, 2);
    $percentileEBP = round($price * 0.75, 2);

    $sql = "INSERT INTO _gg_stock_master
          (_stock_name,_stock_desc,_upc,_rrp,_cbp,_ebp)
          VALUES ('$desc', '$desc','$upc',$price,$percentileCBP,$percentileEBP)";
    $link->query($sql);

    $data = array();

    $data[] = array('desc' => "$desc", 'rrp' => "$price", 'exchangePrice' => "$percentileEBP", 'cashPrice' => "$percentileCBP");
    $api = "/Search for '$upc'.php";
    logRequest($link, $usrAgent, $td, $ip, $api);
    echo json_encode($data);
     * 
     */

}

function getPrice($upc, $link, $usrAgent, $td, $ip) {
    
    include("lib/amazon_api_class.php");

    $obj = new AmazonProductAPI();
    
    //$result = $obj->searchProducts("$upc", AmazonProductAPI::ELECTRONICS, "UPC");
    $upc_code = $upc;
    $product_type = "Electronics";
    $res = $obj->getItemByUpc($upc_code, $product_type);
    
    $xml = new DOMDocument();
    $xml->loadXML($res->saveXML());
    
    
    foreach ($xml->getElementsByTagName('Item') as $node) {

        foreach ($node->getElementsByTagName('Title') as $title) {
            $desc = $title->nodeValue;

        }
        //LowestUsedPrice
        foreach ($node->getElementsByTagName('LowestNewPrice') as $lowPrice) {
               $pr = $lowPrice->nodeValue;
               $pc = $pr / 100;


        }
        // Doing it this way means we get get nodes specific to the item we are on (this is more controlled)    
    }

    $percentileCBP = round($pc * 0.60, 2);

    $percentileEBP = round($pc * 0.75, 2);


 
    $sql = "INSERT INTO _gg_stock_master
          (_stock_name,_stock_desc,_upc,_rrp,_cbp,_ebp)
          VALUES ('$desc', '$desc','$upc',$pc,$percentileCBP,$percentileEBP)";
    $link->query($sql);

    $data = array();

    $data[] = array('desc' => "$desc", 'rrp' => "$pc", 'exchangePrice' => "$percentileEBP", 'cashPrice' => "$percentileCBP");
    $api = "/Search for '$upc'.php";
    logRequest($link, $usrAgent, $td, $ip, $api);
    echo json_encode($data);
    
    
    
    
    
    $link->close();

}

function logRequest($link, $usrAgent, $td, $ip, $api) {
    $reqLogSQL = "INSERT INTO _gg_lg_access
                (_agent,_ip,_req_dt,_api)
                VALUES ('$usrAgent','$ip', '$td','$api')";

    $link->query($reqLogSQL);
}

?>
