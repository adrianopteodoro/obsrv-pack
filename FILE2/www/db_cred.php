<?php
    $serv = 'localhost';
    $datb = 'bioserver2';
    $user = 'bioserver';
    $pass = 'xxxSECUREPASSWORDxxx';
    
    $conn = mysqli_connect($serv, $user, $pass)
        or die ("connection error");

    mysqli_select_db($conn, $datb)
        or die("database failure");
?>