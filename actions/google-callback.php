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

    // Check if user exists
    $stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($userId, $isVerified);
    $stmt->fetch();

    if ($stmt->num_rows == 0) {
        // New user — insert into users table
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, is_verified, otp) VALUES (?, ?, '', 'donor', TRUE, NULL)");
        $stmt->bind_param("ss", $name, $email);
        $stmt->execute();
        $userId = $stmt->insert_id;
        $stmt->close();
    } else {
        // Existing user — update verification
        $stmt->close();
        $stmt = $conn->prepare("UPDATE users SET is_verified = TRUE, otp = NULL WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();
    }

    // Log user in
    $_SESSION['user_id'] = $userId;
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = 'donor';
    $_SESSION['last_activity'] = time();
    $_SESSION['session_expiry'] = 30 * 60;

    // === Onboarding Completion Check ===
    $hasCompleted = false;

    // Step 1: Check donors.organ_type
    $checkDonor = $conn->prepare("SELECT organ_type FROM donors WHERE user_id = ?");
    $checkDonor->bind_param("i", $userId);
    $checkDonor->execute();
    $checkDonor->store_result();
    $checkDonor->bind_result($organType);
    $checkDonor->fetch();

    if ($checkDonor->num_rows === 1 && !empty($organType)) {
        // Step 2: Check hospital selection in user_history
        $checkHospital = $conn->prepare("SELECT selected_hospital FROM user_history WHERE user_id = ? AND selected_hospital IS NOT NULL");
        $checkHospital->bind_param("i", $userId);
        $checkHospital->execute();
        $checkHospital->store_result();

        if ($checkHospital->num_rows > 0) {
            $hasCompleted = true;
        }
        $checkHospital->close();
    }
    $checkDonor->close();
    $conn->close();

    // Redirect based on onboarding completion
    if ($hasCompleted) {
        header("Location: ../templates/match-page.php");
    } else {
        header("Location: ../templates/selector-page.php");
    }
    exit();
} else {
    header("Location: ../templates/login.php?status=error&message=Google login failed!");
    exit();
}
?>
