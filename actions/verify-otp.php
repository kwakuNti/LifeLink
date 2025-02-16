<?php
session_start();
include '../config/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enteredOtp = trim($_POST['otp']);
    $email = $_SESSION['otp_email']; // Email stored during signup

    if (empty($enteredOtp)) {
        header("Location: ../templates/verify-otp.php?status=error&message=OTP is required!");
        exit();
    }

    // Fetch the OTP from the database
    $stmt = $conn->prepare("SELECT otp FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($dbOtp);
    $stmt->fetch();
    $stmt->close();

    if ($enteredOtp === $dbOtp) {
        // Update user as verified and remove OTP
        $stmt = $conn->prepare("UPDATE users SET is_verified = TRUE, otp = NULL WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();

        // Clear session OTP email
        unset($_SESSION['otp_email']);

        // Redirect to login page
        header("Location: ../templates/login.php?status=success&message=Account verified! You can now log in.");
        exit();
    } else {
        // Wrong OTP
        header("Location: ../templates/verify-otp.php?status=error&message=Invalid OTP. Try again.");
        exit();
    }
}
?>
