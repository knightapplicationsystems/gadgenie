<?php

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

try 
{
    //Record Sale

    
    
    //Check that the request comes with a valid token
    $res = $link->query("SELECT * FROM _gg_token WHERE _token = '$token'");
    $num_results = mysqli_num_rows($res);

    if ($num_results < 1) {
        $api = 'Invalid Token';
        logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
        $data = array();

        $data[] = array('resp' => "Token not recognised");


        echo json_encode($data);
        exit;
    }
    $row = mysqli_fetch_array($res, MYSQLI_NUM);

    if ($row[3] < $td) {
        $api = 'Token Expired';
        logRequest($link, $usrAgent, $td, $ip, $api, $reqUrl);
        $data = array();

        $data[] = array('resp' => "Token Expired");

        echo json_encode($data);
        exit;
    } else {
        if ($call == 'login_member') {
            //Login Customer
            
            $username = $_GET['u'];
            
            $password = $_GET['p'];

            login_member($link, $username, $password, $usrAgent, $td, $ip, $reqUrl);
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

            register_customer($link, $fname, $sname, $dob, $add1, $add2, $city, $pcode, $county, $idt, $idd, $uname, $pwd, $email, $hphone, $mphone, $usrAgent, $td, $ip, $reqUrl);
        } else if ($call == 'search_ean') {
            $ean = $_GET['ean'];

            check_stock_master($ean, $link, $usrAgent, $td, $ip,$reqUrl);
        }
        else if($call == 'search_qr')
        {
            $qr = $_GET['qr'];
            
            get_product_info_qr($qr, $link, $usrAgent, $td, $ip, $reqUrl);
        }
        else if ($call == 'search_q_a')
        {
            $q = $_GET['q'];
            
            get_price_by_keyword_amazon($q, $link, $usrAgent, $td, $ip, $reqUrl);
        }
        else if ($call == 'search_q')
        {
            $q = $_GET['q'];
            check_stock_no_ean($q, $link, $usrAgent, $td, $ip, $reqUrl);
        }
        else if ($call == 'check_stock_location')
        {
            $ref= $_GET['r'];
            
            get_current_stock_location($ref, $link, $usrAgent, $td, $ip, $reqUrl);
        }
        else if ($call == 'put_item_in_transit')
        {
            $ref= $_GET['r'];
            $dest = $_GET['d'];
            $usr = $_GET['u'];
            $curr_loc = $_GET['l'];
            
            put_item_in_transit($link,$ref, $dest,$usr,$curr_loc, $link, $usrAgent, $td, $ip, $reqUrl);
        }
        else if ($call == 'get_cust_details')
        {
            $ref= $_GET['r'];
            get_customer_details($link, $ref, $usrAgent, $td, $ip, $reqUrl);
        }
        else if($call == 'add_stock_man')
        {
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
        }
        else if ($call == 'add_stock')
        {
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
            
            add_stock($sName,$sDesc,$cat,$scat,$pic,$upc,$cnd,$col,$percentileCBP,$percentileEBP,$sPrice,$usr,$rec,$pprice,$link, $usrAgent, $td, $ip, $reqUrl);
            
        }
        else if ($call == 'gen_email_purchase')
        {
            $usr = $_GET['u'];
            $rec = $_GET['r'];
            gen_email_purchase($link, $usr, $rec);
        }
        else if ($call == 'get_quant')
        {
            $ean = $_GET['ean'];
            
            check_stock_level_ean($ean, $link, $usrAgent, $td, $ip, $reqUrl);
        }
        else if ($call == 'set_stock_offline_sales')
        {
            $ref = $_GET['r'];
            
            set_stock_offline($ref, $link, $usrAgent, $td, $ip, $reqUrl);
        }
        else if($call == 'get_rec')
        {
            $mem = $_GET['m'];
            $ptype = $_GET['pt'];
            $pref = $_GET['pr'];
            $sbuy = $_GET['u'];
            $tcash = $_GET['tc'];
            $tpoints = $_GET['tp'];
            
            create_receipt($link, $mem, $ptype, $pref, $sbuy, $tcash, $tpoints,$usrAgent, $td, $ip, $reqUrl);
        }
        else if ($call == 'sell_stock_epos')
        {
            $mem = $_GET['m'];
            $route = $_GET['rt'];
            $pref = $_GET['pr'];
            $rec = $_GET['rec'];
            $sPrice = $_GET['sp'];
            $sbuy = $_GET['u'];
            $ref = $_GET['r'];
            
            sell_stock_epos($link, $mem, $route, $pref, $rec, $sPrice, $sbuy, $ref, $usrAgent, $td, $ip, $reqUrl);
            
        }
        else if ($call == 'get_rec_pur')
        {
            $usr = $_GET['u'];
            $tcash = $_GET['tc'];
            $texcg = $_GET['te'];
            $tpoints = $_GET['tp'];
            create_purchase_receipt($link , $usr, $td, $tcash, $texcg, $tpoints, $usrAgent, $td, $ip, $reqUrl);
        }
        else if ($call == 'get_active_print_jobs')
        {
            get_active_print_jobs($link);
        }
        else if ($call == 'update_print_printed')
        {
            $fname = $_GET['f'];
            update_file_printed($link, $fname);
        }
        else if ($call == 'record_sale')
        {
            $cashIn = $_GET['ci'];
            $cashOut = $_GET['co'];
            $cardTotal = $_GET['ct'];
            $authCode = $_GET['ac'];
            $usr = $_GET['u'];
            $ref = $_GET['r'];
            
            log_epos_tran($link, $cashIn, $cashOut, $cardTotal, $authCode, $usr, $ref);
        }
        
        
        
        
    }
} catch (Exception $e) {
    echo json_encode($e);
}
?>
