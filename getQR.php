<?php

function write_qr_to_disk($rec)
{
    $image = file_get_contents('http://chart.apis.google.com/chart?chs=500x500&cht=qr&chld=M&chl=' . urlencode($rec));
    
  
    $filename = 'qr_images/' . $rec . '.png';

    
    file_put_contents($filename, $image);
      
}

function new_qr_per_product($unique_ref)
{
    $image = file_get_contents('http://chart.apis.google.com/chart?chs=500x500&cht=qr&chld=M&chl=' . urlencode($unique_ref));
    
  
    $filename = 'stock_qr_images/' . $unique_ref . '.png';

    
    file_put_contents($filename, $image);
}


?>
