<?php
session_start();
include '../config/connection.php';

// Retrieve POST data
$username = trim($_POST['username'] ?? '');
$newPassword = $_POST['newPassword'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

if (empty($username) || empty($newPassword) || empty($confirmPassword)) {
    header("Location: ../templates/h-forgot-password.php?status=error&message=All fields are required");
    exit();
}

if ($newPassword !== $confirmPassword) {
    header("Location: ../templates/h-forgot-password.php?status=error&message=Passwords do not match");
    exit();
}

// Validate password pattern on the backend as well (optional)
if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $newPassword)) {
    header("Location: ../templates/h-forgot-password.php?status=error&message=Password does not meet requirements");
    exit();
}

// Hash the new password securely
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Update the hospitals table with the new password for the given username
if ($stmt = $conn->prepare("UPDATE hospitals SET password = ? WHERE username = ?")) {
    $stmt->bind_param("ss", $hashedPassword, $username);
    if ($stmt->execute()) {
        header("Location: ../templates/hospital_login.php?status=success&message=Password reset successful. Please log in with your new password");
        exit();
    } else {
        header("Location: ../templates/h-forgot-password.php?status=error&message=Error updating password");
        exit();
    }
    $stmt->close();
} else {
    header("Location: ../templates/h-forgot-password.php?status=error&message=Database error: " . urlencode($conn->error));
    exit();
}

$conn->close();
?>
