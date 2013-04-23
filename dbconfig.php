<?php

    $user = 'root';
    $pass = '522561jh';


    try {
        //$link = new mysqli("localhost", $user, $pass, "_gg");
        $mysqli = new mysqli("localhost", $user, $pass, "_gg");
    } catch (mysqli_sql_exception $e) {
        echo json_encode($e);
    }
    
    return $mysqli;

?>
