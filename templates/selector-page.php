<?php
session_start();
// Check if the user is logged in; if not, redirect to login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: ../templates/login?status=error&message=Please log in first");
    exit();
}
// 2) Include your DB connection
require_once __DIR__ . '/../config/connection.php';

$userId = $_SESSION['user_id'];

// 3) Check if this donor already has an organ_type set
$stmt = $conn->prepare("SELECT organ_type FROM donors WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (!empty($row['organ_type'])) {
        // Already chosen—skip this page and go straight to donor overview
        header("Location: donor_medical_info.php?status=success&message=You have already selected an organ type. Redirecting to your profile.");
        exit();
    }
}
$stmt->close();


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
    <link rel="manifest" href="../favicon_io/site.webmanifest">
    <title>Organ Donation | Select Role</title>
    <link rel="stylesheet" href="../public/css/selector.css">
    <link rel="stylesheet" href="../public/css/snackbar.css">
</head>
<body>
  <!-- Splash Screen -->
  <div id="splash" class="screen visible">
    <h1 class="fade-in">LifeLink</h1>
  </div>
  <div id="snackbar"></div>

<!-- Selector Screen -->
<div id="selector" class="screen hidden">
  <h2 id="typewriter"></h2>
  <div class="options">
    <!-- The 'role' parameter in the query string indicates the chosen role -->
    <a href="../actions/selector-action.php?role=donor" class="option">I Want to Donate</a>
    <a href="../templates/recipient_hospitals" class="option">I Need a Transplant</a>
    </div>
</div>
<script type="text/javascript">
        function checkForMessage() {
            const params = new URLSearchParams(window.location.search);
            if (params.has('status') && params.has('message')) {
                const message = params.get('message');
                const status = params.get('status');
                showSnackbar(message, status);
            }
        }

        function showSnackbar(message, type) {
            let snackbar = document.getElementById("snackbar");
            snackbar.innerHTML = message;
            snackbar.className = "show " + type;
            setTimeout(() => {
                snackbar.className = snackbar.className.replace("show", "");
            }, 3000);
        }
    </script>
    <script src="../public/js/selector.js"></script>
</body>
</html>
