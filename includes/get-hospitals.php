<?php
header('Content-Type: application/json');
include '../config/connection.php';

$region = $_GET['region'] ?? '';
$city = $_GET['city'] ?? '';
$userLat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$userLng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;

if (!$region || $userLat === null || $userLng === null) {
    echo json_encode(["error" => "Incomplete request. Region and coordinates required."]);
    exit();
}

$sql = "
    SELECT id, name, region, city, latitude, longitude, organ_specialty,
    (6371 * ACOS(
      COS(RADIANS(?)) * COS(RADIANS(latitude)) *
      COS(RADIANS(longitude) - RADIANS(?)) +
      SIN(RADIANS(?)) * SIN(RADIANS(latitude))
    )) AS distance
    FROM hospitals
    WHERE region = ?
    ORDER BY distance ASC;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ddds", $userLat, $userLng, $userLat, $region);
$stmt->execute();
$result = $stmt->get_result();

$hospitals = [];
while ($row = $result->fetch_assoc()) {
    $hospitals[] = $row;
}
$stmt->close();
$conn->close();

$response = [
    "user_location" => ["latitude" => $userLat, "longitude" => $userLng],
    "hospitals" => $hospitals
];

echo json_encode($response);
exit();
?>
