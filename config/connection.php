<?php
// config/connection.php

$servername = "localhost";
$username   = "root";
$password   = "Nti2702";
$database   = "life";

// leave out port & socket — mysqli will use XAMPP’s defaults
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
