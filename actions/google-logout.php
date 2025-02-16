<?php
session_start();
require '../config/google-config.php';

// Revoke Google OAuth token if it exists
if (isset($_SESSION['user_id'])) {
    $googleClient->revokeToken();
}

// Destroy session & clear cookies
session_unset();
session_destroy();
setcookie("passkey", "", time() - 3600, "/");

// Redirect to login page
header("Location: ../templates/login.php?status=success&message=Logged out successfully.");
exit();
?>
