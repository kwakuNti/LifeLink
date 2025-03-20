<?php
session_start();
include '../config/connection.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../templates/login.php?status=error&message=Please log in first.");
    exit();
}

// Get the role from the URL
$role = $_GET['role'] ?? '';

// Validate role
if (!in_array($role, ['donor', 'recipient'])) {
    header("Location: ../templates/selector-page.php?status=error&message=Invalid role selected!");
    exit();
}

// Update user's role in the database
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
$stmt->bind_param("si", $role, $userId);

if ($stmt->execute()) {
    // Redirect to the dashboard or any page you want
    header("Location: ../templates/organ-selection.php?status=success&message=Role updated successfully!");
} else {
    header("Location: ../templates/selector-page.php?status=error&message=Failed to update role.");
}

$stmt->close();
$conn->close();
exit();
?>
