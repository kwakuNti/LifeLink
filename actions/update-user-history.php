<?php
session_start();
header('Content-Type: application/json');
include '../config/connection.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "User not logged in."]);
    exit();
}

$userId = $_SESSION['user_id'];

// Retrieve POST parameters (make sure your frontend sends these as POST data)
$region = $_POST['region'] ?? '';
$city = $_POST['city'] ?? '';
$latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
$longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;
$selectedHospital = $_POST['selected_hospital'] ?? null; // Optional field if a hospital was chosen

// Validate required parameters
if (!$region || !$city || !$latitude || !$longitude) {
    echo json_encode(["error" => "Missing required location parameters."]);
    exit();
}

// Update the user's location in the users table
$stmt = $conn->prepare("UPDATE users SET region = ?, city = ?, latitude = ?, longitude = ? WHERE id = ?");
if (!$stmt) {
    echo json_encode(["error" => "Database error: " . $conn->error]);
    exit();
}
$stmt->bind_param("ssddi", $region, $city, $latitude, $longitude, $userId);
if (!$stmt->execute()) {
    echo json_encode(["error" => "Failed to update user location."]);
    exit();
}
$stmt->close();

// Insert a record into the user_history table to track this search
$stmt = $conn->prepare("INSERT INTO user_history (user_id, selected_region, selected_city, latitude, longitude, selected_hospital) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(["error" => "Database error: " . $conn->error]);
    exit();
}
$stmt->bind_param("issddi", $userId, $region, $city, $latitude, $longitude, $selectedHospital);
if (!$stmt->execute()) {
    echo json_encode(["error" => "Failed to insert user search history."]);
    exit();
}
$stmt->close();

$conn->close();

echo json_encode(["success" => true, "message" => "User location and history updated successfully."]);
exit();
?>
