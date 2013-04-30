
<?php

$dateFrom = $_GET['dateFrom'];
$dateToo = $_GET['dateToo'];



try {
    $link = include 'dbconfig.php';
} catch (mysqli_sql_exception $e) {
    echo json_encode($e);
}

check_daily_sales($link,$dateFrom,$dateToo);

function check_daily_sales($link,$dateFrom,$dateToo)
{
    $res = $link->query("SELECT * 
                        FROM _gg_sales
                        WHERE _dt_sold > '$dateFrom 00:01:00'
                        AND _dt_sold <= '$dateToo 23:59:59'");


    echo "<h>Here are the current stock items that have passed 6 hours in transit";
    echo "<br>";
    while ($row = mysqli_fetch_array($res)) {
            
           
            echo "<br>";
            echo "Item Reference: " . $row["_item_ref"];
            echo "<br>";
            echo "Date Sold: " . $row["_dt_sold"];
            echo "<br>";
            echo "Sale Price: £" . $row["_sale_price"];
            echo "<br>";
            echo "Order Ref: " . $row["_order_ref"];
            echo "<br>";
            }
    
    
    
}

?>
