<?php
// config/connection.php

// XAMPP MySQL credentials
$servername = "127.0.0.1";  // force TCP
$port       = 3306;         // default MySQL port
$username   = "root";
$password   = "";           // XAMPP root has no password
$database   = "life";

// Create connection over TCP
$conn = new mysqli(
    $servername,
    $username,
    $password,
    $database,
    $port
);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
