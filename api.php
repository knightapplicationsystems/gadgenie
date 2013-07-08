<?php

header('Content-type: application/json');
require 'api_functions.php';

$usrAgent = $_SERVER['HTTP_USER_AGENT'];
$reqUrl = $_SERVER['REQUEST_URI'];
$ip = $_SERVER['REMOTE_ADDR'];
$td = date("Y-m-d H:i:s");
$token = $_GET['tkn'];
$call = $_GET['call'];

try {
    $link = include 'dbconfig.php';
} catch (mysqli_sql_exception $e) {
    echo json_encode($e);
}

try {
    //Record Sale
    //Check that the request comes with a valid token
    $res = $link->query("SELECT * FROM _gg_token WHERE _token = '$token'");
    $num_results = mysqli_num_rows($res);

    if ($num_results < 1) {
        $api = 'Invalid Token';
        logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
        header("HTTP/1.0 403 Invalid Token", true, 403);
        exit;
    }
    $row = mysqli_fetch_array($res, MYSQLI_NUM);

    if ($row[3] < $td) {
        $api = 'Token Expired';
        logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
        header("HTTP/1.0 403 Token Expired", true, 403);
        exit;
    } else {
        if ($call == 'login_customer') {
            //Login Customer

            if (is_null($_GET['u'])) {
                $username = 'np';
            } else {
                $username = $_GET['u'];
            }

            $password = $_GET['p'];

            if (is_null($_GET['provider'])) {
                $pro = 'np';
            } else {
                $pro = $_GET['provider'];
            }


            if (is_null($_GET['uid'])) {
                $uid = 'np';
            } else {
                $uid = $_GET['uid'];
            }

            login_member($link, $username, $password, $pro, $uid, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'register_staff') {
            //Register Staff

            $fname = $_GET['f'];

            $sname = $_GET['s'];

            $code = $_GET['c'];

            $pwd = $_GET['p'];

            $admin = $_GET['a'];

            $sales = $_GET['sa'];

            $bo = $_GET['bo'];

            register_staff($link, $fname, $sname, $code, $pwd, $admin, $sales, $bo, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'login_staff') {

            $username = $_GET['u'];

            $password = $_GET['p'];

            login_staff($link, $username, $password, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'register_customer') {

            $fname = $_GET['forename'];

            $sname = $_GET['surname'];

            $dob = $_GET['dob'];

            $add1 = $_GET['add1'];

            $add2 = $_GET['add2'];

            $city = $_GET['city'];

            $pcode = $_GET['pc'];

            $county = $_GET['county'];

            $idt = $_GET['idtype'];

            $idd = $_GET['iddata'];

            $uname = $_GET['u'];

            $pwd = $_GET['p'];

            $email = $_GET['email'];

            $hphone = $_GET['homephone'];

            $mphone = $_GET['mobilephone'];

            if (is_null($_GET['provider'])) {
                $pro = 'np';
            } else {
                $pro = $_GET['provider'];
            }

            if (is_null($_GET['uid'])) {
                $uid = 'np';
            } else {
                $uid = $_GET['uid'];
            }

            register_customer($link, $fname, $sname, $dob, $add1, $add2, $city, $pcode, $county, $idt, $idd, $uname, $pwd, $email, $hphone, $mphone, $pro, $uid, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'update_customer') {

            $memID = $_GET['memID'];

            $updateArray = array();

            if (is_null($_GET['forename'])) {
                $fname = 'np';
            } else {
                $fname = $_GET['forename'];
                array_push($updateArray, '_fname=' . "'" . $fname . "'");
            }

            if (is_null($_GET['surname'])) {
                $sname = 'np';
            } else {
                $sname = $_GET['surname'];
                array_push($updateArray, '_sname=' . "'" . $sname . "'");
            }

            if (is_null($_GET['dob'])) {
                $dob = 'np';
            } else {
                $dob = $_GET['dob'];
                array_push($updateArray, '_dob=' . "'" . $dob . "'");
            }

            if (is_null($_GET['add1'])) {
                $add1 = 'np';
            } else {
                $add1 = $_GET['add1'];
                array_push($updateArray, '_add1=' . "'" . $add1 . "'");
            }

            if (is_null($_GET['add2'])) {
                $add2 = 'np';
            } else {
                $add2 = $_GET['add2'];
                array_push($updateArray, '_add2=' . "'" . $add2 . "'");
            }

            if (is_null($_GET['city'])) {
                $city = 'np';
            } else {
                $city = $_GET['city'];
                array_push($updateArray, '_town=' . "'" . $city . "'");
            }

            if (is_null($_GET['pc'])) {
                $pcode = 'np';
            } else {
                $pcode = $_GET['pc'];
                array_push($updateArray, '_pcode=' . "'" . $pcode . "'");
            }

            if (is_null($_GET['county'])) {
                $county = 'np';
            } else {
                $county = $_GET['county'];
                array_push($updateArray, '_county=' . "'" . $county . "'");
            }

            if (is_null($_GET['idtype'])) {
                $idt = 'np';
            } else {
                $idt = $_GET['idtype'];
                array_push($updateArray, '__id_type=' . "'" . $idt . "'");
            }

            if (is_null($_GET['iddata'])) {
                $idd = 'np';
            } else {
                $idd = $_GET['iddata'];
                array_push($updateArray, '_id_detail=' . "'" . $idd . "'");
            }

            if (is_null($_GET['u'])) {
                $uname = 'np';
            } else {
                $uname = $_GET['u'];
                array_push($updateArray, '_uname=' . "'" . $uname . "'");
            }

            if (is_null($_GET['email'])) {
                $email = 'np';
            } else {
                $email = $_GET['email'];
                array_push($updateArray, '_email=' . "'" . $email . "'");
            }

            if (is_null($_GET['homephone'])) {
                $hphone = 'np';
            } else {
                $hphone = $_GET['homephone'];
                array_push($updateArray, '_hphone=' . "'" . $hphone . "'");
            }

            if (is_null($_GET['mobilephone'])) {
                $mphone = 'np';
            } else {
                $mphone = $_GET['mobilephone'];
                array_push($updateArray, '_mphone=' . "'" . $mphone . "'");
            }

            if (is_null($_GET['provider'])) {
                $pro = 'np';
            } else {
                $pro = $_GET['provider'];
                array_push($updateArray, '_provider=' . "'" . $pro . "'");
            }

            if (is_null($_GET['uid'])) {
                $uid = 'np';
            } else {
                $uid = $_GET['uid'];
                array_push($updateArray, '_uid=' . "'" . $uid . "'");
            }


            update_customer_record($memID, $link, $updateArray, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'search_ean') {
            $ean = $_GET['ean'];

            check_stock_master($ean, $link, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'get_product') {
            $ean = $_GET['ean'];

            check_stock_master_web($ean, $link, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'search_qr') {
            $qr = $_GET['qr'];

            get_product_info_qr($qr, $link, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'search_q_a') {
            $q = $_GET['q'];

            $ptype = "All";

            get_price_by_keyword_amazon($q, $ptype, $link, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'search_q') {
            $q = $_GET['q'];

            check_stock_no_ean($q, $link, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'search_buy') {
            $q = $_GET['q'];
            check_stock_no_ean($q, $link, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'search_sell') {
            $q = $_GET['q'];
            $ptype = $_GET['category'];
            get_price_by_keyword_amazon($q, $ptype, $link, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'check_stock_location') {
            $ref = $_GET['r'];

            get_current_stock_location($ref, $link, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'put_item_in_transit') {
            $ref = $_GET['r'];
            $dest = $_GET['d'];
            $usr = $_GET['u'];
            $curr_loc = $_GET['l'];

            put_item_in_transit($link, $ref, $dest, $usr, $curr_loc, $link, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'get_cust_details') {
            $ref = $_GET['r'];
            get_customer_details($link, $ref, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'add_stock_man') {
            $sname = $_GET['s'];
            $sdesc = $_GET['d'];
            $ean = $_GET['e'];
            $rrp = $_GET['r'];
            $cat = $_GET['c'];
            $scat = $_GET['sc'];

            //Cash buy price
            $cbp = round($rrp * 0.60, 2);
            //Exchange Buy Price
            $ebp = round($rrp * 0.75, 2);

            add_stock_man($sname, $sdesc, $ean, $rrp, $cbp, $ebp, $cat, $scat, $link, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'add_stock') {
            $sName = $_GET['sn'];
            $sDesc = $_GET['sd'];
            $cat = $_GET['ct'];
            $scat = $_GET['sct'];
            $pic = $_GET['pc'];
            $upc = $_GET['upc'];
            $cnd = $_GET['cn'];
            $col = $_GET['co'];
            $percentileCBP = $_GET['cbp'];
            $percentileEBP = $_GET['ebp'];
            $sPrice = $_GET['sp'];
            $pprice = $_GET['pp'];
            $usr = $_GET['us'];
            $rec = $_GET['rec'];

            add_stock($sName, $sDesc, $cat, $scat, $pic, $upc, $cnd, $col, $percentileCBP, $percentileEBP, $sPrice, $usr, $rec, $pprice, $link, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'add_to_basket_sell') {
            $ean = $_GET['ean'];
            $memID = $_GET['memID'];
            add_to_basket_sell($link, $ean, $memID, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'add_to_basket_buy') {
            $scode = $_GET['stock_code'];
            $memID = $_GET['memID'];
            add_to_basket_buy($link, $scode, $memID, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'add_to_ownlist') {
            $ean = $_GET['ean'];
            $memID = $_GET['memID'];
            add_to_own_list($link, $ean, $memID, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'add_to_wishlist') {
            $ean = $_GET['ean'];
            $memID = $_GET['memID'];
            add_to_wishlist($link, $ean, $memID, $usrAgent, $td, $ip, $reqUrl);
            
        }else if ($call == 'get_basket_sell') {
            $memID = $_GET['memID'];
            get_basket_sell($link, $memID, $usrAgent, $td, $ip, $api, $reqUrl);
        } else if ($call == 'get_basket_buy') {
            $memID = $_GET['memID'];

        } else if ($call == 'get_ownlist') {
            $memID = $_GET['memID'];

        } else if ($call == 'get_wishlist') {
            $memID = $_GET['memID'];

            
        } else if ($call == 'gen_email_purchase') {
            $usr = $_GET['u'];
            $rec = $_GET['r'];
            $etype = $_GET['et'];
            gen_email_purchase($link, $usr, $rec, $etype);
        } else if ($call == 'get_quant') {
            $ean = $_GET['ean'];

            check_stock_level_ean($ean, $link, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'set_stock_offline_sales') {
            $ref = $_GET['r'];

            set_stock_offline($ref, $link, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'get_rec') {
            $mem = $_GET['m'];
            $ptype = $_GET['pt'];
            $pref = $_GET['pr'];
            $sbuy = $_GET['u'];
            $tcash = $_GET['tc'];
            $tpoints = $_GET['tp'];

            create_receipt($link, $mem, $ptype, $pref, $sbuy, $tcash, $tpoints, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'sell_stock_epos') {
            $mem = $_GET['m'];
            $route = $_GET['rt'];
            $pref = $_GET['pr'];
            $rec = $_GET['rec'];
            $sPrice = $_GET['sp'];
            $sbuy = $_GET['u'];
            $ref = $_GET['r'];

            sell_stock_epos($link, $mem, $route, $pref, $rec, $sPrice, $sbuy, $ref, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'get_rec_pur') {
            $usr = $_GET['u'];
            $tcash = $_GET['tc'];
            $texcg = $_GET['te'];
            $tpoints = $_GET['tp'];
            create_purchase_receipt($link, $usr, $td, $tcash, $texcg, $tpoints, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'get_active_print_jobs') {
            get_active_print_jobs($link);
        } else if ($call == 'update_print_printed') {
            $fname = $_GET['f'];
            update_file_printed($link, $fname);
        } else if ($call == 'record_sale') {
            $cashIn = $_GET['ci'];
            $cashOut = $_GET['co'];
            $cardTotal = $_GET['ct'];
            $authCode = $_GET['ac'];
            $usr = $_GET['u'];
            $ref = $_GET['r'];

            log_epos_tran($link, $cashIn, $cashOut, $cardTotal, $authCode, $usr, $ref);
        } else if ($call == 'add_to_print_queue') {
            
        } else if ($call == 'get_all') {
            get_all_cust($link);
        } else {
            header("HTTP/1.0 404 Invalid API call", true, 404);
        }
    }
} catch (Exception $e) {
    echo json_encode($e);
}
?>
