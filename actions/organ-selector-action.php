<?php
session_start();
include '../config/connection.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../templates/login?status=error&message=Please log in first.");
    exit();
}

$userId = $_SESSION['user_id'];
$organ = $_POST['organ'] ?? '';

if (!$organ || !in_array($organ, ['Kidney', 'Liver'])) {
    header("Location: ../templates/organ-selector-page?status=error&message=Please select a valid organ.");
    exit();
}

// Update the user's role to donor (if not already set)
$stmt = $conn->prepare("UPDATE users SET role = 'donor' WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->close();

// Insert or update the donor record with the selected organ type
$stmt = $conn->prepare("SELECT id FROM donors WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    // Donor record exists, update it
    $stmt->close();
    $stmt = $conn->prepare("UPDATE donors SET organ_type = ? WHERE user_id = ?");
    $stmt->bind_param("si", $organ, $userId);
    $stmt->execute();
} else {
    // Insert new donor record
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO donors (user_id, organ_type) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $organ);
    $stmt->execute();
}
$stmt->close();
$conn->close();

// Redirect based on the organ selected
if ($organ === 'Kidney') {
    header("Location: ../templates/donor_medical_info?status=success&message=Organ selection saved.");
} else if ($organ === 'Liver') {
    header("Location: ../templates/liver_donor_info?status=success&message=Organ selection saved.");
}
exit();
?>
