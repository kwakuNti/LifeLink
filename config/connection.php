<?php
$servername = "localhost";  // Change if using a remote database
$username = "root";         // Change if using a different MySQL user
$password = "";             // Change if you have a password
$database = "life";         // Our database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $database);
