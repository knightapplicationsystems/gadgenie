<?php


function add_points_to_account($cust_ref,$sale_value)
{
    try
    {  
        $link = include 'dbconfig.php';
    }
    catch (mysqli_sql_exception $e)
    {
        echo json_encode($e);
    }
    
    $points_to_award = $sale_value * 0.02 * 100;
    
    $link->query("UPDATE _gg_cust SET _points_balance = $points_to_award WHERE _memNumber = '$cust_ref'");
    
    
}









?>
