<?php
session_start();
require '../config/google-config.php';

$authUrl = $googleClient->createAuthUrl();
header("Location: $authUrl");
exit();
?>
