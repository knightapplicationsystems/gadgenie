<?php

try {
    $link = include 'dbconfig.php';
} catch (mysqli_sql_exception $e) {
    echo json_encode($e);
}

check_stock_transit_state($link);

function check_stock_transit_state($link)
{
    $td = date("Y-m-d H:i:s");
    $res = $link->query("SELECT * 
                        FROM _gg_tran_log
                        WHERE _date_in IS NULL
                        AND _warn_time < '$td'");

    echo "<h>Here are the current stock items that have passed 6 hours in transit";
    echo "<br>";
    while ($row = mysqli_fetch_array($res)) {
            
           
            echo "<br>";
            echo "Item Reference: " . $row["_item_ref"];
            echo "<br>";
            echo "Origin: " . $row["_origin"];
            echo "<br>";
            echo "Destination: " . $row["_dest"];
            echo "<br>";
            echo "Date Out: " . $row["_date_out"];
            echo "<br>";
            }
    
    
    
}
    






?>
