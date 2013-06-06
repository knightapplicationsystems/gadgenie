<?php

//Login Customer
function login_member($link, $username, $password, $pro, $uid, $usrAgent, $td, $ip, $reqUrl) {

//Get Salt and Password
    if ($pro == 'np') {
        $fst = $link->query("SELECT * FROM _gg_cust WHERE _uname = '$username'");
        //CDreate Array to check valid login
        $row = mysqli_fetch_array($fst, MYSQLI_NUM);
//Get the Salt from the table
        $salt = $row[19];
//Hash the salt and password together to verify in the database
        $deErb = hash('sha1', "$salt$password");
//Check DB for the above
        $verify = $link->query("SELECT * FROM _gg_cust WHERE _uname = '$username' AND _pwd = '$deErb'");
//Check valid login details
        $num_results = mysqli_num_rows($verify);
        if ($num_results < 1) {
            $data = array();

            $data[] = array('resp' => "Login invalid");

            echo json_encode($data);
            $api = 'Invalid Login';
            logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
            exit;
        } else {
//Array variable for the JSON response
            $data = array();
//Go through the result set and return the customer details
            while ($nRow = mysqli_fetch_array($verify)) {
                $data[] = array('memID' => $nRow["_memNumber"], 'fname' => $nRow["_fname"], 'sname' => $nRow["_sname"], 'dob' => $nRow["_dob"], 'add1' => $nRow["_add1"],
                    'add2' => $nRow["_add2"], 'town' => $nRow["_town"], 'pcode' => $nRow["_pcode"], 'hphone' => $nRow["_hphone"], 'mphone' => $nRow["_mphone"]
                    , 'email' => $nRow["_email"], 'country' => $nRow["_country"], 'county' => $nRow["_county"]);
            }
//Send a JSON RESP back
            echo json_encode($data);
//Audit
            $api = "User $username logged in ok";
            logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
        }
    } else {
        //Check DB for the above
        $verify = $link->query("SELECT * FROM _gg_cust WHERE _uid = '$uid'");
//Check valid login details
        $num_results = mysqli_num_rows($verify);
        if ($num_results < 1) {
            $data = array();

            $data[] = array('resp' => "Login invalid");

            echo json_encode($data);
            $api = 'Invalid Login';
            logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
            exit;
        } else {
//Array variable for the JSON response
            $data = array();
//Go through the result set and return the customer details
            while ($nRow = mysqli_fetch_array($verify)) {
                $data[] = array('memID' => $nRow["_memNumber"], 'fname' => $nRow["_fname"], 'sname' => $nRow["_sname"], 'dob' => $nRow["_dob"], 'add1' => $nRow["_add1"],
                    'add2' => $nRow["_add2"], 'town' => $nRow["_town"], 'pcode' => $nRow["_pcode"], 'hphone' => $nRow["_hphone"], 'mphone' => $nRow["_mphone"]
                    , 'email' => $nRow["_email"], 'country' => $nRow["_country"], 'county' => $nRow["_county"]);
            }
//Send a JSON RESP back
            echo json_encode($data);
//Audit
            $api = "User $username logged in ok";
            logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
        }
    }
}

//Login Staff
function login_staff($link, $username, $password, $usrAgent, $td, $ip, $reqUrl) {
//Get Salt and Password
    $fst = $link->query("SELECT * FROM _gg_staff WHERE _code = '$username'");
//CDreate Array to check valid login
    $row = mysqli_fetch_array($fst, MYSQLI_NUM);
//Get the Salt from the table
    $salt = $row[8];
//Hash the salt and password together to verify in the database
    $deErb = hash('sha1', "$salt$password");
//Check DB for the above
    $verify = $link->query("SELECT * FROM _gg_staff WHERE _code = '$username' AND _pwd = '$deErb'");
//Check valid login details
    $num_results = mysqli_num_rows($verify);
    if ($num_results < 1) {
        $data = array();

        $data[] = array('resp' => "Login invalid");

        echo json_encode($data);
//Audit
        $api = 'Invalid Login';
        logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
        exit;
    } else {
//Array variable for the JSON response
        $data = array();
//Go through the result set and return the customer details
        while ($nRow = mysqli_fetch_array($verify)) {
            $data[] = array('code' => $nRow["_code"], 'fname' => $nRow["_fname"], 'sname' => $nRow["_sname"], 'admin' => $nRow["_is_admin"],
                'sales' => $nRow["_is_sales"], 'back_office' => $nRow["_is_back_office"]);
        }
//Send a JSON RESP back
        echo json_encode($data);
//Audit
        $api = "Staff $username logged in ok";
        logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
    }
}

//Register Staff
function register_staff($link, $fname, $sname, $code, $pwd, $admin, $sales, $bo, $usrAgent, $td, $ip, $reqUrl) {
//Get a Salt
    $salt = require_once 'saltGen.php';
//Apply Salt to the Password
    $saltPass = "$salt$pwd";
//Hash the combined password
    $saltedPass = hash('sha1', $saltPass);
//Create new staff member
    $sql = "INSERT INTO _gg_staff
                    (_fname,_sname,_code,_pwd,_is_admin,_is_sales,_is_back_office,_salt)
                    VALUES
                    ('$fname','$sname',$code,'$saltedPass',$admin,$sales,$bo,'$salt')";
//Insert into DB
    $link->query($sql);
//Create Array for response
    $data = array();
//Respond Succesful Staff Member
    $data[] = array('staff_added' => "$code");
//Respond JSON
    echo json_encode($data);
//Audit
    $api = "Register new staff member $code";
    logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
}

//New Customer Registration
function register_customer($link, $fname, $sname, $dob, $add1, $add2, $city, $pcode, $county, $idt, $idd, $uname, $pwd, $email, $hphone, $mphone,$pro,$uid, $usrAgent, $td, $ip, $reqUrl) {
//Get a mem ID
    $memID = require_once 'uniqueGen.php';
//Get a Salt
    $salt = require_once 'saltGen.php';
//Apply Salt to the Password
    $saltPass = "$salt$pwd";
//Hash the combined password
    $saltedPass = hash('sha1', $saltPass);
//SQL Query
    $sql = "INSERT INTO _gg_cust
                    (_memNumber,_fname,_sname,_dob,_add1,_add2,_town,_pcode,_hphone,_mphone,_email,
                    _country,_county,_uname,_salt,_pwd,_id_type,_id_detail,_provider,_uid)
                    VALUES
                    ('$memID','$fname','$sname','$dob','$add1','$add2','$city','$pcode','$hphone',
                     '$mphone','$email','UK', '$county','$uname','$salt','$saltedPass','$idt','$idd','$pro','$uid')";

    $link->query($sql);

    $data = array();
//Respond JSON
    $data[] = array('memID' => "$memID");

    echo json_encode($data);
//Audit
    $api = "New Customer $fname $sname added";
    logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
}

function get_product_info_qr($qr, $link, $usrAgent, $td, $ip, $reqUrl) {
    $checkSQL = $link->query("SELECT * 
                FROM _gg_stock
                WHERE _code_id = '$qr'");

    $row = mysqli_fetch_array($checkSQL, MYSQLI_NUM);

    if ($row[2] == '') {
        
    } else {
        $data = array();
        $checkSQL1 = $link->query("SELECT * 
                                    FROM _gg_stock
                                    WHERE _code_id = '$qr'");

        while ($nrow = mysqli_fetch_array($checkSQL1)) {
            $points = $nrow["_sp"] * 1000;
            $data[] = array('stock_name' => $nrow["_stock_name"], 'stock_desc' => $nrow["_stock_desc"],
                'upc' => $nrow["_barcode"],
                'sale_price' => $nrow["_sp"], 'cash_price' => $nrow["_cbp"], 'exchange_price' => $nrow["_ebp"],
                'sub_cat' => $nrow["_sub_cat"], 'points' => "$points");
        }

        $api = "Search for $qr";
        logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
        echo json_encode($data);
    }
}

function get_price_by_ean_amazon($ean, $link, $usrAgent, $td, $ip, $reqUrl) {
//Include Amazon API
    include("lib/amazon_api_class.php");
//Create new Amazon instance
    $obj = new AmazonProductAPI();
//Search all products
    $product_type = "All";
//Search XML
    $res = $obj->getItemByEAN($ean, $product_type);
    $xml = new DOMDocument();
    $xml->loadXML($res->saveXML());
//Loop through XML nodes for the response
    $data = array();
    foreach ($xml->getElementsByTagName('Item') as $node) {
        foreach ($node->getElementsByTagName('SalesRank') as $rank) {
            $asr = $rank->nodeValue;
        }
//Title
        foreach ($xml->getElementsByTagName('ItemAttributes') as $node) {
            foreach ($node->getElementsByTagName('Title') as $title) {
                $titDesc = $title->nodeValue;
                $desc = mysqli_real_escape_string($link, $titDesc);
            }
            foreach ($node->getElementsByTagName('Binding') as $topCat) {
                $category = $topCat->nodeValue;
                $item_cat = mysqli_real_escape_string($link, $category);
            }
        }
        foreach ($xml->getElementsByTagName('OfferSummary') as $node) {
//Product Category
//LowestUsedPrice
            foreach ($node->getElementsByTagName('LowestUsedPrice') as $lowPrice) {
                $pr = $lowPrice->nodeValue;
                $pc = $pr / 100;
                $points = $pc * 1000;
                if ($pc < 0.30) {
                    $pc = 0.30;
                }
                if ($item_cat == 'Blu-ray' || $item_cat == 'DVD' || $item_cat == 'Audio CD') {
                    //Cash buy price
                    $percentileCBP = round($pc * 0.32, 2);
                    //Exchange Buy Price
                    $percentileEBP = round($pc * 0.40, 2);
                } else if ($item_cat == 'Video Game') {
                    //Cash buy price
                    $percentileCBP = round($pc * 0.62, 2);
                    //Exchange Buy Price
                    $percentileEBP = round($pc * 0.75, 2);
                } else {
                    if ($asr <= 999) {
                        //Cash buy price
                        $percentileCBP = round($pc * 0.50, 2);
                        //Exchange Buy Price
                        $percentileEBP = round($pc * 0.65, 2);
                    } else if ($asr >= 1000 && $asr <= 9999) {
                        //Cash buy price
                        $percentileCBP = round($pc * 0.35, 2);
                        //Exchange Buy Price
                        $percentileEBP = round($pc * 0.50, 2);
                    } else if ($asr > 10000) {
                        //Cash buy price
                        $percentileCBP = round($pc * 0.20, 2);
                        //Exchange Buy Price
                        $percentileEBP = round($pc * 0.35, 2);
                    }
                }
            }
        }
        $data[] = array('stock_name' => "$desc",
            'stock_desc' => "$desc", 'sale_price' => "$pc", 'exchange_price' => "$percentileEBP",
            'cash_price' => "$percentileCBP", 'upc' => "$ean", 'sub_cat' => "$item_cat", 'points' => "$points");
    }



//Create new record in the DB
    $sql = "INSERT INTO _gg_stock_master
          (_stock_name,_stock_desc,_sub_cat,_upc,_rrp,_cbp,_ebp,_asr)
          VALUES ('$desc', '$desc','$item_cat',$ean,$pc,$percentileCBP,$percentileEBP,$asr)";
    $link->query($sql);


//Audit
    $api = "Item $ean $desc added to the Database";
    logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
//JSON Response
    echo json_encode($data);
}

function get_price_by_keyword_amazon($q, $link, $usrAgent, $td, $ip, $reqUrl) {
    include("lib/amazon_api_class.php");

    $obj = new AmazonProductAPI();
    $product_type = "All";
    try {
        $result = $obj->getItemByKeyword($q, $product_type);
    } catch (Exception $e) {
        echo $e->getMessage();
    }

    $xml = new DOMDocument();
    $xml->loadXML($result->saveXML());

    $cUPC = '';
    $cTitle = '';
    $cRRP = '';
    $data = array();

    foreach ($xml->getElementsByTagName('Item') as $topNode) {
        foreach ($topNode->getElementsByTagName('SalesRank') as $rank) {
            $asr = $rank->nodeValue;
        }

        foreach ($topNode->getElementsByTagName('ItemAttributes') as $node) {
            foreach ($node->getElementsByTagName('Title') as $title) {
                $cTitle = ($title->nodeValue);
            }
            foreach ($node->getElementsByTagName('EAN') as $ean) {
                $cUPC = ($ean->nodeValue);
            }
        }
        foreach ($topNode->getElementsByTagName('OfferSummary') as $node) {


            //LowestUsedPrice
            foreach ($node->getElementsByTagName('LowestNewPrice') as $lowPrice) {
                $cRRP = ($lowPrice->nodeValue);
                $pc = $cRRP / 100;
                if ($asr <= 999) {
                    //Cash buy price
                    $percentileCBP = round($pc * 0.50, 2);
                    //Exchange Buy Price
                    $percentileEBP = round($pc * 0.65, 2);
                } else if ($asr >= 1000 && $asr <= 9999) {
                    //Cash buy price
                    $percentileCBP = round($pc * 0.35, 2);
                    //Exchange Buy Price
                    $percentileEBP = round($pc * 0.50, 2);
                } else if ($asr > 10000) {
                    //Cash buy price
                    $percentileCBP = round($pc * 0.20, 2);
                    //Exchange Buy Price
                    $percentileEBP = round($pc * 0.35, 2);
                }
            }
        }
        $data[] = array('stock_name' => "$cTitle",
            'ean' => "$cUPC", 'rrp' => "$pc",
            'exchangePrice' => "$percentileEBP",
            'cashPrice' => "$percentileCBP");
    }


    echo json_encode($data);
    $api = "Search for $q";
    logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
}

function add_stock_man($sname, $sdesc, $ean, $rrp, $cbp, $ebp, $cat, $scat, $link, $usrAgent, $td, $ip, $reqUrl) {
    $ref = require_once 'uniqueGen.php';

    $link->query("INSERT INTO _gg_stock(_code_id,_stock_name,_stock_desc,_barcode,_dt_add,_sp,_cbp,_ebp,_cat,_sub_cat)
VALUES
('$ref','$sname','$sdesc',$ean,'$td',$rrp,$cbp,$ebp,'$cat','$scat')");

    $link->query("INSERT INTO _gg_stock_master
(_stock_name,_stock_desc,_upc,_rrp,_cbp,_ebp,_cat,_sub_cat)
VALUES
('$sname','$sdesc',$ean,$rrp,$cbp,$ebp,'$cat','$scat')");

    $data = array();

    $data[] = array('code' => "$ref");
    echo json_encode($data);

    $api = "Item $sname Manually added into DB";
    logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
}

function add_stock($sName, $sDesc, $cat, $scat, $pic, $upc, $cnd, $col, $percentileCBP, $percentileEBP, $sPrice, $usr, $rec, $pprice, $link, $usrAgent, $td, $ip, $reqUrl) {

    $unique_ref = require_once 'uniqueGen.php';
    require_once 'getQR.php';

    new_qr_per_product($unique_ref);

    $name = mysqli_real_escape_string($link, $sName);
    $desc = mysqli_real_escape_string($link, $sDesc);
    $cat = mysqli_real_escape_string($link, $cat);
    $scat = mysqli_real_escape_string($link, $scat);

    $sql = "INSERT INTO _gg_stock
    (_code_id,_stock_name,_stock_desc,_cat,_sub_cat,_pic,_barcode,_cond,_col,_dt_add,_cbp,_ebp,_sp,_add_by,_pprice,_rec_ref)
    VALUES ('$unique_ref','$name', '$desc','$cat','$scat',
    '$pic','$upc','$cnd','$col','$td',$percentileCBP,$percentileEBP,$sPrice,'$usr',$pprice,'$rec')";



    $link->query($sql);
    $api = "Stock $name added to Stock Levels";
    logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);

    $data = array();

    $data[] = array('code' => "$unique_ref");

    echo json_encode($data);
}

function gen_email_purchase($link, $usr, $rec, $etype) {
    $verify = $link->query("SELECT * FROM _gg_cust WHERE _memNumber = '$usr'");
    $row = mysqli_fetch_array($verify, MYSQLI_NUM);
    $usr = $row[0];
    $email = $row[15];

    add_to_email_queue($link, $email, $rec, $etype);
}

//Check existing stock before trying Amazon
function check_stock_master($ean, $link, $usrAgent, $td, $ip, $reqUrl) {
    $checkSQL = $link->query("SELECT * FROM _gg_stock_master WHERE _upc = '$ean'");
    $row = mysqli_fetch_array($checkSQL, MYSQLI_NUM);

    if ($row[3] == '') {
        get_price_by_ean_amazon($ean, $link, $usrAgent, $td, $ip, $reqUrl);
    } else {
        $data = array();
        $checkSQLL = $link->query("SELECT * FROM _gg_stock_master WHERE _upc = '$ean'");
        while ($nrow = mysqli_fetch_array($checkSQLL)) {
            $point = $nrow["_rrp"];
            $points = $point * 1000;
            $data[] = array('stock_name' => $nrow["_stock_name"], 'stock_desc' => $nrow["_stock_desc"],
                'upc' => $nrow["_upc"],
                'sale_price' => $nrow["_rrp"], 'cash_price' => $nrow["_cbp"], 'exchange_price' => $nrow["_ebp"],
                'sub_cat' => $nrow["_sub_cat"], 'points' => "$points");
        }
        echo json_encode($data);
        $api = "Search Stock Master for $ean";
        logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
    }
}

//Check existing stock before trying Amazon
function check_stock_level_ean($ean, $link, $usrAgent, $td, $ip, $reqUrl) {

    $data = array();
    $checkSQLL = $link->query("SELECT * FROM _gg_stock WHERE _barcode = '$ean' AND _epos = 0");
    while ($nrow = mysqli_fetch_array($checkSQLL)) {
        $data[] = array('stock_name' => $nrow["_stock_name"], 'stock_desc' => $nrow["_stock_desc"],
            'upc' => $nrow["_barcode"],
            'sale_price' => $nrow["_rrp"], 'cash_price' => $nrow["_cbp"], 'exchange_price' => $nrow["_ebp"],
            'sub_cat' => $nrow["_sub_cat"]);
    }
    echo json_encode($data);
    $api = "Get Stock Quantity for $ean";
    logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
}

function set_stock_offline($ref, $link, $usrAgent, $td, $ip, $reqUrl) {
    $link->query("UPDATE _gg_stock SET _epos = 1 WHERE _code_id = '$ref'");
    $api = "Item $ref updated to sell offline";
    logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
}

function check_stock_no_ean($q, $link, $usrAgent, $td, $ip, $reqUrl) {
    $checkSQL = $link->query("SELECT * 
FROM _gg_stock_master
WHERE MATCH (
_stock_name
)
AGAINST (
'$q'
IN BOOLEAN
MODE
)");

    $row = mysqli_fetch_array($checkSQL, MYSQLI_NUM);

    if ($row[2] == '') {
        get_price_by_keyword_amazon($q, $link, $usrAgent, $td, $ip, $reqUrl);
    } else {
        $data = array();
        $checkSQL1 = $link->query("SELECT * 
FROM _gg_stock_master
WHERE MATCH (
_stock_name
)
AGAINST (
'$q'
IN BOOLEAN
MODE
)");

        while ($nrow = mysqli_fetch_array($checkSQL1)) {
            $data[] = array('stock_name' => $nrow["_stock_name"], 'stock_desc' => $nrow["_stock_desc"], 'ean' => $nrow["_upc"],
                'rrp' => $nrow["_rrp"], 'cash_price' => $nrow["_cbp"], 'exchange_price' => $nrow["_ebp"]);
        }
        $api = "Search for $q";
        logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
        echo json_encode($data);
    }
}

function get_customer_details($link, $ref, $usrAgent, $td, $ip, $reqUrl) {

    $verify = $link->query("SELECT * FROM _gg_cust WHERE _memNumber = '$ref'");
//Check valid login details
    $num_results = mysqli_num_rows($verify);
    if ($num_results < 1) {
        $data = array();

        $data[] = array('resp' => "Customer Not Found");

        echo json_encode($data);
        $api = 'Invalid Customer Ref';
        logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
        exit;
    } else {
//Array variable for the JSON response
        $data = array();
//Go through the result set and return the customer details
        while ($nRow = mysqli_fetch_array($verify)) {
            $data[] = array('fname' => $nRow["_fname"], 'sname' => $nRow["_sname"],
                'dob' => $nRow["_dob"], 'add1' => $nRow["_add1"],
                'add2' => $nRow["_add2"], 'town' => $nRow["_town"], 'pcode' => $nRow["_pcode"],
                'hphone' => $nRow["_hphone"], 'mphone' => $nRow["_mphone"]
                , 'email' => $nRow["_email"], 'county' => $nRow["_county"]);
        }
//Send a JSON RESP back
        echo json_encode($data);
//Audit
        $api = "Customer record accessed $ref";
        logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
    }
}

//Stock movement controls
function get_current_stock_location($ref, $link, $usrAgent, $td, $ip, $reqUrl) {
    $resp = $link->query("SELECT * FROM _gg_stock WHERE _code_id = '$ref'");
    $row = mysqli_fetch_array($resp, MYSQLI_NUM);

    if ($row[21] == 0) {
        $data = array();
        $data[] = array('resp' => "Item is currently in Transit");
        echo json_encode($data);
    }

    $api = "Check location for $ref";
    logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
}

function put_item_in_transit($link, $ref, $dest, $curr_loc, $usr, $usrAgent, $td, $ip, $reqUrl) {
    $link->query("UPDATE _gg_stock SET _curr_loc = 0 WHERE _code_id = '$ref'");
    new_tran_log($link, $ref, $dest, $curr_loc, $td, $usr, $usrAgent, $td, $ip, $reqUrl);
}

function update_tran_log($link, $ref, $td, $usr) {
    $link->query("UPDATE _gg_tran_log
SET _date_in = '$td', usr = '$usr' WHERE _date_out != '$td' AND _item_ref= '$ref'");
}

function log_epos_tran($link, $cashIn, $cashOut, $cardTotal, $authCode, $usr, $ref) {
    $reqLogSQL = "INSERT INTO _gg_epos_trans
                (_cash_in,_cash_out,_user,_auth_code,_card_total,_rec_ref)
                VALUES ($cashIn,$cashOut,$usr,$authCode,$cardTotal,'$ref')";
    //echo $reqLogSQL;
    $link->query($reqLogSQL);
}

function sell_stock_epos($link, $mem, $route, $pref, $rec, $sPrice, $sbuy, $ref, $usrAgent, $td, $ip, $reqUrl) {
    $exp = date('Y-m-d H:i:s', strtotime('+1 year'));

    $link->query("UPDATE _gg_stock
                    SET _pymnt_ref = '$pref',_warr_end = '$exp',_dt_sold = '$td',_sold_to = '$mem', _route = '$route',
                        _order_ref = '$rec',_sp = $sPrice,_sold_by = '$sbuy',_picked=1,_dispatched=1
                    WHERE _code_id = '$ref'");

    log_sales_record($link, $rec, $ref, $sPrice, $usrAgent, $td, $ip, $reqUrl);
}

function log_sales_record($link, $rec, $td, $ref, $sPrice, $usrAgent, $td, $ip, $reqUrl) {
    $reqLogSQL = "INSERT INTO _gg_sales
                (_order_ref,_dt_sold,_item_ref,_sale_price)
                VALUES ('$rec','$td', '$ref',$sPrice)";
    //echo $reqLogSQL;
    $link->query($reqLogSQL);



    $api = "ITEM SOLD $ref";
    logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
}

function create_receipt($link, $mem, $ptype, $pref, $sbuy, $tcash, $tpoints, $usrAgent, $td, $ip, $reqUrl) {
    $rec = require_once 'uniqueGen.php';
    require_once 'gen_purchase_receipt.php';
    require_once 'getQR.php';

    $link->query("INSERT INTO _gg_receipt (_rec_ref,_memNumber,_pymnt_type,_pymnt_ref,_sold_by
        ,_total_cash,_total_points,_dt_sold)
        VALUES ('$rec','$mem','$ptype','$pref','$sbuy',$tcash,$tpoints,'$td')");


    $data = array();
    $data[] = array('receipt_ref' => $rec);
    echo json_encode($data);

    write_qr_to_disk($rec);

    gen_new_receipt($rec, $link);

    $api = "Receipt generated by $sbuy";
    logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
}

function create_purchase_receipt($link, $usr, $td, $tcash, $texcg, $tpoints, $usrAgent, $td, $ip, $reqUrl) {
    $rec = require_once 'uniqueGen.php';

    $link->query("INSERT INTO _gg_purchased (_rec_ref,_add_by,_dt_add
        ,_total_cash,_total_exchange,_total_points)
        VALUES ('$rec','$usr','$td',$tcash,$texcg,$tpoints)");

    $data = array();
    $data[] = array('receipt_ref' => $rec);
    echo json_encode($data);

    $api = "Purchase Receipt generated by $usr";
    logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
}

function add_to_email_queue($link, $email, $rec, $etype) {
    $link->query("INSERT INTO _gg_emailq (_email,_email_type,_sent,_rec) VALUES ('$email','$etype',0,'$rec')");
}

function new_tran_log($link, $ref, $dest, $curr_loc, $td, $usr, $usrAgent, $td, $ip, $reqUrl) {
    $exp = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    $link->query("INSERT INTO _gg_tran_log
(_item_ref,_origin,_dest,_date_out,_warn_time,_usr)
VALUES
('$ref',$curr_loc,$dest,'$td','$exp','$usr')");

    $api = "Item $ref put in transit to $dest";
    logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
//$link->close();
}

function get_active_print_jobs($link) {
    $resp = $link->query("SELECT * FROM _gg_label_jobs WHERE _is_printed = 0");
    $num_results = mysqli_num_rows($resp);
    if ($num_results < 1) {
        $data = array();

        $data[] = array('resp' => "No Jobs");

        echo json_encode($data);

        exit;
    } else {
//Array variable for the JSON response
        $data = array();
//Go through the result set and return the customer details
        while ($nRow = mysqli_fetch_array($resp)) {
            $data[] = array('file_name' => $nRow["_file_name"]);
        }
//Send a JSON RESP back
        echo json_encode($data);
    }
}

function update_file_printed($link, $fname) {
    $link->query("UPDATE _gg_label_jobs
                    SET _is_printed = 1
                    WHERE _file_name = '$fname'");
}

function add_to_print_queue() {
    
}

//Audit Log
function logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl) {
    $reqLogSQL = "INSERT INTO _gg_lg_access
(_agent,_ip,_req_dt,_api,_uri)
VALUES ('$usrAgent','$ip', '$td','$api','$reqUrl')";

    $link->query($reqLogSQL);
}

?>
