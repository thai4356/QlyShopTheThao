<?php
    $servername = "localhost:3306";
    $username = "root";
    $password = "root";
    $dbname = "user_database";
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $conn;

?>