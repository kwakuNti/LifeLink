<?php
session_start();
include '../config/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $rememberMe = isset($_POST["rememberMe"]); // Check if "Remember Me" is checked

    // Validate input
    if (empty($email) || empty($password)) {
        header("Location: ../templates/login.php?status=error&message=Email and password are required!");
        exit();
    }

    // Fetch user from database
    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($userId, $fullName, $dbEmail, $hashedPassword);
    $stmt->fetch();

    if ($stmt->num_rows == 1 && password_verify($password, $hashedPassword)) {
        // Set session variables
        $_SESSION['user_id'] = $userId;
        $_SESSION['name'] = $fullName;
        $_SESSION['email'] = $dbEmail;
        $_SESSION['last_activity'] = time();  // Store session start time
        $_SESSION['session_expiry'] = 30 * 60; // 30 minutes timeout

        // Generate a passkey if "Remember Me" is checked
        if ($rememberMe) {
            $passkey = bin2hex(random_bytes(32));  // Secure random key
            setcookie("passkey", $passkey, time() + (7 * 24 * 60 * 60), "/", "", false, true);

            // Store hashed passkey in database
            $hashedPasskey = password_hash($passkey, PASSWORD_DEFAULT);
            $updateToken = $conn->prepare("UPDATE users SET passkey = ?, last_login = NOW() WHERE id = ?");
            $updateToken->bind_param("si", $hashedPasskey, $userId);
            $updateToken->execute();
            $updateToken->close();
        }

        header("Location: ../templates/dashboard.php");
    } else {
        header("Location: ../templates/login.php?status=error&message=Invalid email or password!");
    }

    $stmt->close();
    $conn->close();
}
?>
