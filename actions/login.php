<?php
session_start();
include '../config/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $rememberMe = isset($_POST["rememberMe"]);

    if (empty($email) || empty($password)) {
        header("Location: ../templates/login?status=error&message=Email and password are required!");
        exit();
    }

    // Fetch user from database
    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($userId, $fullName, $dbEmail, $hashedPassword, $role);
    $stmt->fetch();

    if ($stmt->num_rows == 1 && password_verify($password, $hashedPassword)) {
        // Set session variables
        $_SESSION['user_id'] = $userId;
        $_SESSION['name'] = $fullName;
        $_SESSION['email'] = $dbEmail;
        $_SESSION['role'] = $role;
        $_SESSION['last_activity'] = time();
        $_SESSION['session_expiry'] = 30 * 60; // 30 mins

        // "Remember Me" functionality
        if ($rememberMe) {
            $passkey = bin2hex(random_bytes(32));
            setcookie("passkey", $passkey, time() + (7 * 24 * 60 * 60), "/", "", false, true);

            $hashedPasskey = password_hash($passkey, PASSWORD_DEFAULT);
            $updateToken = $conn->prepare("UPDATE users SET passkey = ?, last_login = NOW() WHERE id = ?");
            $updateToken->bind_param("si", $hashedPasskey, $userId);
            $updateToken->execute();
            $updateToken->close();
        }

        // === Check Onboarding Progress ===
        $hasCompleted = false;

        if ($role === 'donor') {
            // Check organ_type in donors table
            $checkDonor = $conn->prepare("SELECT organ_type FROM donors WHERE user_id = ?");
            $checkDonor->bind_param("i", $userId);
            $checkDonor->execute();
            $checkDonor->store_result();
            $checkDonor->bind_result($organType);
            $checkDonor->fetch();

            if ($checkDonor->num_rows == 1 && !empty($organType)) {
                // Check hospital selection
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
        }

        $conn->close();

        if ($hasCompleted) {
            header("Location: ../templates/match-page");
        } else {
            header("Location: ../templates/selector-page");
        }

    } else {
        header("Location: ../templates/login?status=error&message=Invalid email or password!");
        exit();
    }

    $stmt->close();
}
?>
