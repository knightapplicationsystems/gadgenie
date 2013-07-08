<?php

try {
    $link = include 'dbconfig.php';
} catch (mysqli_sql_exception $e) {
    echo json_encode($e);
}

$checkSQL1 = $link->query("SELECT *
                    FROM _gg_stock_master");

while ($nrow = mysqli_fetch_array($checkSQL1)) {
    $sname = $nrow["_stock_name"];
    $slug=  seoUrl($sname);
    $slug1 = strtolower($slug);
    $slug2 = $slug1."-".$nrow["_upc"];
    
    $link->query("UPDATE _gg_stock_master SET _slug='$slug2' WHERE _stock_name = '$sname'");
    
}


function seoUrl($string) {
    //Unwanted:  {UPPERCASE} ; / ? : @ & = + $ , . ! ~ * ' ( )
    $string = strtolower($string);
    //Strip any unwanted characters
    $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
    //Clean multiple dashes or whitespaces
    $string = preg_replace("/[\s-]+/", " ", $string);
    //Convert whitespaces and underscore to dash
    $string = preg_replace("/[\s_]/", "-", $string);
    return $string;
}


//$slug=preg_replace('/[^A-Za-z0-9-]+/', '-', $string);



?>
