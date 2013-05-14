<?php

function write_qr_to_disk($unique_ref,$email)
{
    $image = file_get_contents('http://chart.apis.google.com/chart?chs=500x500&cht=qr&chld=M&chl=' . urlencode($unique_ref));
    
  
    $filename = 'qr_images/' . $unique_ref . '.png';

    
    file_put_contents($filename, $image);
    
    require_once 'gen_packing_slip.php';
    
    //Local
    //$getQR = 'C:\\wamp\\www\\gadgenie\\api\\lib\\stock_qr\\' . $unique_ref . '.png';
    //Server
    $getQR = 'qr_images/' . $unique_ref . '.png';
    
    gen_new_packing_slip($getQR,$unique_ref,$email);
    
}


?>
