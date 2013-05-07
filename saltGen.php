<?php

//This class generates a Salt to use when registering customers
$unique_ref_length = 64;
$possible_chars = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

$salt = "";

$i = 0;
while ($i < $unique_ref_length) {

    // Pick a random character from the $possible_chars list  
    $char = substr($possible_chars, mt_rand(0, strlen($possible_chars) - 1), 1);

    $salt .= $char;

    $i++;
}
return $salt;
?>
