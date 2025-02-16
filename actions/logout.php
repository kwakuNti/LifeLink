<?php
session_start();
session_unset();
session_destroy();

// Clear passkey cookie
setcookie("passkey", "", time() - 3600, "/");

// Redirect to login page
header("Location: ../templates/login.php?status=success&message=Logged out successfully.");
exit();
?>
