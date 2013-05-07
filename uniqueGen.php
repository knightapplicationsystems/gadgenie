<?php

//MemID
$unique_ref_length_id = 10;
$possible_chars = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
$unique_ref = "";

$c = 0;

while ($c < $unique_ref_length_id) {

    // Pick a random character from the $possible_chars list  
    $char = substr($possible_chars, mt_rand(0, strlen($possible_chars) - 1), 1);

    $unique_ref .= $char;

    $c++;
}

return $unique_ref;
?>
