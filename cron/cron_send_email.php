<?php

//$dir = dirname(__FILE__);
//echo "<p>Full path to this dir: " . $dir . "</p>";

require('emailer.php');

try {
    $link = include '../dbconfig.php';
} catch (mysqli_sql_exception $e) {
    echo json_encode($e);
}

$verify = $link->query("SELECT * FROM _gg_emailq WHERE _sent = 0");
//echo $verify;
//Go through the result set and return the customer details
while ($nRow = mysqli_fetch_array($verify)) {

    $email = $nRow["_email"];
    $mailType = $nRow["_email_type"];
    if ($mailType == 'add_stock')
    {
        $url = "http://gadgenie.com/api/cust_docs/purchase_receipts/" . $nRow["_rec"] . '.pdf';
        $mailType = 'Your Purchase Receipt';
    }
    
    
    send_new_email($email, $mailType, $url);
    
    $link->query("UPDATE _gg_emailq SET  _sent = 1");

    
    
}






?>
