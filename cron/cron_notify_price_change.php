<?php

$td = date("Y-m-d H:i:s");
try {
    $link = include '../dbconfig.php';
} catch (mysqli_sql_exception $e) {
    echo json_encode($e);
}
$verify = $link->query("SELECT * FROM _gg_stock_master WHERE _price_changed <= '$td' AND _notified = 0");
//echo $verify;
//Go through the result set and return the customer details
while ($nRow = mysqli_fetch_array($verify)) {

    $upc = $nRow["_upc"];
    $old_price = $nRow["_old_price"];
    $new_price = $nRow["_rrp"];
    $sname = $nRow["_stock_name"];
    $dt = $nRow["_price_changed"];

    $link->query("INSERT INTO _gg_price_change_pending (_ref,_old_price,_new_price,_date)
                    VALUES ($upc,$old_price,$new_price,'$dt')");
    
    $link->query("UPDATE _gg_stock SET _sp = $new_price WHERE _barcode = $upc OR _stock_name = '$sname'");
    $link->query("UPDATE _gg_stock_master SET _notified = 1 WHERE _price_changed <= '$td' AND _notified = 0");
    
    
}
?>
