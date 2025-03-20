<?php
$servername = "localhost";  // Change if using a remote database
$username = "root";         // Default MAMP MySQL username
$password = "root";         // Default MAMP MySQL password
$database = "life";         // Your database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>