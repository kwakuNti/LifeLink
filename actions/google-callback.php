<?php
use Google_Service_Oauth2;
session_start();
require '../config/google-config.php';
include '../config/connection.php';

if (isset($_GET['code'])) {
    $token = $googleClient->fetchAccessTokenWithAuthCode($_GET['code']);
    $googleClient->setAccessToken($token['access_token']);

    // Get user profile from Google
    $googleService = new Google_Service_Oauth2($googleClient);
    $googleUser = $googleService->userinfo->get();

    $googleId = $googleUser->id;
    $name = $googleUser->name;
    $email = $googleUser->email;

    // Check if user exists in database
    $stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($userId, $isVerified);
    $stmt->fetch();

    if ($stmt->num_rows == 0) {
        // If user doesn't exist, register them as verified
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, is_verified, otp) VALUES (?, ?, '', 'donor', TRUE, NULL)");
        $stmt->bind_param("ss", $name, $email);
        $stmt->execute();
        $userId = $stmt->insert_id; // Get the newly created user ID
        $stmt->close();
    } else {
        // If user exists, update verification status and remove OTP
        $stmt = $conn->prepare("UPDATE users SET is_verified = TRUE, otp = NULL WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();
    }

    // Log the user in
    $_SESSION['user_id'] = $userId;
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;

    header("Location: ../templates/selector-page.php");
    exit();
} else {
    header("Location: ../templates/login.php?status=error&message=Google login failed!");
    exit();
}
?>