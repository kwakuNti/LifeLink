<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

  // at the very top of /opt/lampp/htdocs/LifeLink/index.php
  session_start();
  if (!isset($_SESSION['user_id'])) {
    header("Location: /templates/homepage/");
    exit();
  }