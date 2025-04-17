<?php
$servername = "localhost";  // Still localhost on the same instance
$username = "root";         // Default XAMPP MySQL user
$password = "";             // XAMPP on Linux has no root password by default
$database = "life";         // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
