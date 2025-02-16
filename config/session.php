<?php
session_start();
include '../config/connection.php';

$timeout = 30 * 60;

if (isset($_SESSION['user_id'])) {
    if (time() - $_SESSION['last_activity'] > $timeout) {
        session_unset();
        session_destroy();
        header("Location: ../templates/login.php?status=error&message=Session expired!");
        exit();
    }

    $_SESSION['last_activity'] = time();
} elseif (isset($_COOKIE['passkey'])) {
    // Quick login using passkey
    $passkey = $_COOKIE['passkey'];
    
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE passkey = ?");
    $stmt->bind_param("s", $passkey);
    $stmt->execute();
    $stmt->bind_result($userId, $name, $email);
    
    while ($stmt->fetch()) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['last_activity'] = time();
        header("Location: ../templates/dashboard.php");
        exit();
    }

    $stmt->close();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../templates/login.php");
    exit();
}
?>
