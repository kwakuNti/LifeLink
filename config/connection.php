<?php
// config/connection.php

// XAMPP MySQL credentials
$servername = "127.0.0.1";  // force TCP (so we don’t accidentally try the system socket)
$port       = 3306;         // XAMPP’s MySQL port
$username   = "root";
$password   = "Nti2702";    // your new root password
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
