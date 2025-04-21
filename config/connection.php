<?php
// config/connection.php

$servername = "127.0.0.1";
$username   = "root";
$password   = "Nti2702";
$database   = "life";
$socket     = "/opt/lampp/var/mysql/mysql.sock";

$conn = new mysqli(
    $servername,
    $username,
    $password,
    $database,
    $socket
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
