<?php
// config/connection.php

// XAMPP MySQL credentials
$servername = "localhost";  // force TCP (so we don’t accidentally try the system socket)
$username   = "root";
$password   = "Nti2702";    // your new root password
$database   = "life";

// Create connection over TCP
$conn = new mysqli(
    $servername,
    $username,
    $password,
    $database,
);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
