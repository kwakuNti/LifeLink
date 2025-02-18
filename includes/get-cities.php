<?php
header('Content-Type: application/json'); // Ensure JSON output
include '../config/connection.php';

// Enable error logging to a file instead of displaying on screen
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php-error.log');

$region = $_GET['region'] ?? '';

if (!$region) {
    echo json_encode([]); // Return an empty array if no region is provided
    exit();
}

$query = "SELECT DISTINCT city FROM hospitals WHERE region = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(["error" => "Database query failed: " . $conn->error]);
    exit();
}

$stmt->bind_param("s", $region);
$stmt->execute();
$result = $stmt->get_result();

$cities = [];
while ($row = $result->fetch_assoc()) {
    $cities[] = $row['city'];
}

$stmt->close();
$conn->close();

echo json_encode($cities);
exit();
?>
