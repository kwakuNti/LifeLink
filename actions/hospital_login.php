<?php
session_start();
include '../config/connection.php';

// Get POST data
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header("Location: ../templates/hospital_login.php?status=error&message=Missing credentials");
    exit();
}

// Prepare a statement to fetch the hospital by username
if ($stmt = $conn->prepare("SELECT id, name, password FROM hospitals WHERE username = ?")) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $hospital = $result->fetch_assoc();
        
        // Check if the hospital's stored password is still the default.
        // Here we compare using password_verify() against "Default@1".
        if (password_verify("Default@1", $hospital['password'])) {
            header("Location: ../templates/hospital_login.php?status=error&message=Please click 'Forgot Password' to set a new password");
            exit();
        }
        
        // Verify provided password
        if (password_verify($password, $hospital['password'])) {
            // Successful login: set session variables
            $_SESSION['hospital_id'] = $hospital['id'];
            $_SESSION['hospital_name'] = $hospital['name'];
            header("Location: ../templates/hospital-admin.php?status=success&message=Welcome " . urlencode($hospital['name']));
            exit();
        } else {
            header("Location: ../templates/hospital_login.php?status=error&message=Invalid credentials");
            exit();
        }
    } else {
        header("Location: ../templates/hospital_login.php?status=error&message=Invalid credentials");
        exit();
    }
    $stmt->close();
} else {
    header("Location: ../templates/hospital_login.php?status=error&message=Database error: " . urlencode($conn->error));
    exit();
}

$conn->close();
?>
