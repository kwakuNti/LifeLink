<?php
header('Content-Type: application/json'); // Ensure JSON output
include '../config/connection.php';

// Enable error logging to a file instead of displaying on screen
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php-error.log');

$region = $_GET['region'] ?? '';
$city = $_GET['city'] ?? '';
$manualLocation = $_GET['location'] ?? '';

// Check if at least one location parameter is provided
if (!$region && !$city && !$manualLocation) {
    echo json_encode(["error" => "No location provided"]); 
    exit();
}

// Initialize user latitude and longitude
$userLat = null;
$userLng = null;

// If manual location is provided, convert it to lat/lng using Google Geocoding API
if (!empty($manualLocation)) {
    $geocodeUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($manualLocation)."AIzaSyDlzRfWUaPWKVws7I8Y7iTqk5_kYl3ZYm4";
    $geoResponse = file_get_contents($geocodeUrl);
    $geoData = json_decode($geoResponse, true);

    if (!empty($geoData['results'])) {
        $userLat = $geoData['results'][0]['geometry']['location']['lat'];
        $userLng = $geoData['results'][0]['geometry']['location']['lng'];
    } else {
        echo json_encode(["error" => "Invalid location entered"]);
        exit();
    }
}

// If no manual location, check database for region/city coordinates
if (!$userLat || !$userLng) {
    $sqlLocation = "SELECT latitude, longitude FROM hospitals WHERE region = ? AND city = ? LIMIT 1";
    $stmtLocation = $conn->prepare($sqlLocation);
    if (!$stmtLocation) {
        echo json_encode(["error" => "Database query failed: " . $conn->error]);
        exit();
    }

    $stmtLocation->bind_param("ss", $region, $city);
    $stmtLocation->execute();
    $resultLocation = $stmtLocation->get_result();
    
    if ($row = $resultLocation->fetch_assoc()) {
        $userLat = $row['latitude'];
        $userLng = $row['longitude'];
    } else {
        echo json_encode(["error" => "No matching location found in the database"]);
        exit();
    }
}

// Fetch hospitals and calculate distance using Haversine formula
$sql = "
    SELECT id, name, region, city, latitude, longitude, organ_specialty,
    (6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(latitude)) 
    * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) 
    * SIN(RADIANS(latitude)))) AS distance
    FROM hospitals
    ORDER BY distance ASC
    LIMIT 3;
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["error" => "Database query failed: " . $conn->error]);
    exit();
}

$stmt->bind_param("ddd", $userLat, $userLng, $userLat);
$stmt->execute();
$result = $stmt->get_result();

$hospitals = [];
while ($row = $result->fetch_assoc()) {
    $hospitals[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($hospitals);
exit();
?>
