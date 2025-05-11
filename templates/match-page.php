<?php
include_once '../config/connection.php';
session_start();

// at the very top, after session_start() etc.
define('API_BASE', 
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . '/api/'
);

// Check if user is logged in and is a donor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'donor') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get donor information – join users and donors tables
$query = "SELECT 
            d.id, 
            d.init_age,  
            d.bmi_tcr, 
            d.dayswait_alloc, 
            d.kidney_cluster, 
            d.dgn_tcr, 
            d.wgt_kg_tcr, 
            d.hgt_cm_tcr, 
            d.gfr, 
            d.on_dialysis, 
            d.blood_type, 
            d.organ_type,
            d.created_at, 
            u.name, 
            u.email 
          FROM donors d 
          JOIN users u ON d.user_id = u.id 
          WHERE d.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();
$stmt->close();

if (!$donor) {
    $error = "Donor profile not found. Please complete your profile first.";
}

// Function to call our Flask API (for actions handled via PHP)
function callAPI($endpoint, $data = []) {
  $api_url = API_BASE . $endpoint;        // ← now goes via lifelink.ink/api/…
  $curl    = curl_init($api_url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_TIMEOUT, 30);

  if (!empty($data)) {
      $jsonData = json_encode($data);
      curl_setopt($curl, CURLOPT_POST,        true);
      curl_setopt($curl, CURLOPT_POSTFIELDS,  $jsonData);
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
          'Content-Type: application/json',
          'Content-Length: ' . strlen($jsonData)
      ]);
  }

  $response = curl_exec($curl);
  $err      = curl_error($curl);
  curl_close($curl);

  if ($err) {
      return ["error" => "cURL Error: " . $err];
  }

  return json_decode($response, true);
}


// Helper function to check if a match already exists
function isAlreadyMatched($donor_id, $recipient_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM matches WHERE donor_id = ? AND recipient_id = ?");
    $stmt->bind_param("ii", $donor_id, $recipient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $matched = ($result->fetch_assoc() != null);
    $stmt->close();
    return $matched;
}

// Helper function to check if a transplant is already confirmed for a given match
function isTransplantConfirmed($match_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM transplants WHERE match_id = ?");
    $stmt->bind_param("i", $match_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $confirmed = ($result->fetch_assoc() != null);
    $stmt->close();
    return $confirmed;
}

// Initialize variables for match and prediction handling
$matches = [];
$prediction = null;
$showMatches = false;
$showPrediction = false;
$recipient_id = null;
$selectedMatch = null;
$message = "";
$error = "";

// Handle Find Matches button click (server-side)
if (isset($_POST['find_matches'])) {
    $donor_id = isset($donor['id']) ? $donor['id'] : null;
    if ($donor_id) {
        $response = callAPI("find_matches", ["donor_id" => $donor_id]);
        if (isset($response['matches'])) {
            $matches = $response['matches'];
            $showMatches = true;
        } else if (isset($response['error'])) {
            $error = $response['error'];
        }
    } else {
        $error = "Donor ID not found. Please complete your profile first.";
    }
}

// Handle Predict Success button click (server-side)
if (isset($_POST['predict_success']) && isset($_POST['recipient_id'])) {
    $recipient_id = $_POST['recipient_id'];
    $donor_id = isset($donor['id']) ? $donor['id'] : null;
    if ($donor_id) {
        $response = callAPI("predict_success", [
            "donor_id" => $donor_id,
            "recipient_id" => $recipient_id
        ]);
        if (!isset($response['error'])) {
            $prediction = $response;
            $showPrediction = true;
            foreach ($matches as $match) {
                if ($match['id'] == $recipient_id) {
                    $selectedMatch = $match;
                    break;
                }
            }
        } else {
            $error = $response['error'];
        }
    } else {
        $error = "Donor ID not found. Please complete your profile first.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
  <!--  ▾  put these AFTER your other <script> tags in the <head>  -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

  <link rel="manifest" href="../favicon_io/site.webmanifest">
  <title>LifeLink - Donor Match Finder</title>
  <style>

:root {
  --primary: #4070E0;
  --primary-hover: #3055B0;
  --primary-light: #EDF2FF;
  --primary-transparent: rgba(64, 112, 224, 0.1);
  --success: #34a853;
  --warning: #fbbc05;
  --danger: #ea4335;
  --gray-100: #f8f9fa;
  --gray-200: #EFF3FB;
  --gray-300: #e6e6e6;
  --gray-500: #6B7A99;
  --gray-900: #333;
  --white: #ffffff;
  --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
  --radius-sm: 12px;
  --radius-md: 16px;
  --radius-full: 50%;
  --transition-base: all 0.3s ease;
  --spacing-xs: 0.5rem;
  --spacing-sm: 1rem;
  --spacing-md: 1.5rem;
  --spacing-lg: 2rem;
  --spacing-xl: 3rem;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', sans-serif;
  min-height: 100vh;
  background-color: var(--gray-200);
  color: var(--gray-900);
  line-height: 1.6;
}

h1, h2, h3 {
  font-weight: 600;
  line-height: 1.2;
}
h1 {
  font-size: 1.75rem;
  color: var(--primary);
  margin-bottom: var(--spacing-sm);
}
h2 {
  font-size: 1.25rem;
  margin-bottom: var(--spacing-md);
  position: relative;
  padding-bottom: 10px;
}
h2::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: 0;
  height: 3px;
  width: 40px;
  background-color: var(--primary);
  border-radius: 2px;
}
h3 {
  font-size: 1.125rem;
}

p {
  margin-bottom: var(--spacing-sm);
}

.container {
  width: 100%;
  max-width: 1440px;
  margin: 0 auto;
  padding: 0 var(--spacing-sm);
}

/* Navbar */
.navbar {
  padding: var(--spacing-md);
  background-color: var(--white);
  box-shadow: var(--shadow-sm);
  position: sticky;
  top: 0;
  z-index: 100;
}
.navbar-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.logo img {
  height: 50px;
  width: auto;
}
.user-profile {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}
.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-full);
  background-color: var(--primary-light);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary);
  font-weight: 600;
}

/* Main Layout */
.main-container {
  display: grid;
  grid-template-columns: 1fr 350px; 
  gap: var(--spacing-xl); /* Increased gap for better separation */
  padding: var(--spacing-md) var(--spacing-sm);
}
@media (max-width: 1100px) {
  .main-container {
    grid-template-columns: 1fr; 
  }
  .match-panel {
  min-width: 0; /* Allows proper flex sizing */
}

/* Added spacing adjustments */
.donor-details {
  gap: var(--spacing-md);
  margin-top: var(--spacing-md);
}

.matching-container {
  gap: var(--spacing-lg);
}
}
@media (max-width: 768px) {
  .main-container {
    grid-template-columns: 1fr;
  }
}

.card {
  background: var(--white);
  border-radius: var(--radius-md);
  padding: var(--spacing-md);
  box-shadow: var(--shadow-sm);
  transition: var(--transition-base);
  height: 100%;
}
.card:hover {
  box-shadow: var(--shadow-md);
}

/* Criteria List */
.criteria-list {
  margin: var(--spacing-md) 0;
  list-style: none;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-xs);
}
.criteria-list li {
  margin-bottom: var(--spacing-xs);
  padding-left: var(--spacing-md);
  position: relative;
  display: flex;
  align-items: center;
}
.criteria-list li::before {
  content: "✓";
  color: var(--success);
  position: absolute;
  left: 0;
}

/* Stats */
.stats-container {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--spacing-sm);
  margin: var(--spacing-md) 0;
}
.stat-box {
  background: var(--primary-light);
  padding: var(--spacing-sm);
  border-radius: var(--radius-sm);
  text-align: center;
  transition: var(--transition-base);
  position: relative;
  overflow: hidden;
}
.stat-box::after {
  content: '';
  position: absolute;
  width: 100%;
  height: 4px;
  bottom: 0;
  left: 0;
  background: var(--primary);
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.3s ease;
}
.stat-box:hover {
  transform: translateY(-5px);
}
.stat-box:hover::after {
  transform: scaleX(1);
}
.stat-box h3 {
  color: var(--primary);
  font-size: 1.5rem;
  font-weight: 700;
}

/* Match Panel */
.match-panel {
  text-align: center;
}
.donor-profile {
  background: var(--primary-light);
  border-radius: var(--radius-sm);
  padding: var(--spacing-md);
  margin-bottom: var(--spacing-md);
  display: flex;
  flex-direction: column;
  align-items: center;
}
.donor-avatar {
  width: 80px;
  height: 80px;
  border-radius: var(--radius-full);
  background-color: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--white);
  font-size: 2rem;
  font-weight: 600;
  margin-bottom: var(--spacing-sm);
}
.donor-details {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: var(--spacing-sm);
  margin-top: var(--spacing-sm);
}
.donor-detail {
  background: var(--white);
  padding: 5px 15px;
  border-radius: 20px;
  font-size: 0.9rem;
  box-shadow: var(--shadow-sm);
}
.match-button {
  width: 100%;
  max-width: 320px;
  height: 56px;
  background-color: var(--primary);
  color: var(--white);
  border: none;
  border-radius: var(--radius-sm);
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-xs);
  transition: var(--transition-base);
  margin: var(--spacing-lg) auto;
  position: relative;
  overflow: hidden;
}
.match-button:hover {
  background-color: var(--primary-hover);
  transform: translateY(-2px);
}
.match-button::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 5px;
  height: 5px;
  background: rgba(255, 255, 255, 0.5);
  opacity: 0;
  border-radius: var(--radius-full);
  transform: translate(-50%, -50%) scale(1);
  transition: width 0.6s ease-out, height 0.6s ease-out, opacity 0.6s ease-out;
}
.match-button:hover::after {
  width: 300px;
  height: 300px;
  opacity: 0.2;
}
.match-button.loading {
  pointer-events: none;
  position: relative;
}
.match-button.loading svg {
  display: none;
}
.match-button.loading::before {
  content: "";
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: var(--white);
  animation: spin 1s ease-in-out infinite;
  position: absolute;
  left: calc(50% - 30px);
  top: calc(50% - 10px);
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Matching Results */
.matching-container {
  margin: var(--spacing-md) 0;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: var(--spacing-md);
}
.match-card {
  border: 1px solid var(--gray-300);
  border-radius: var(--radius-sm);
  padding: var(--spacing-md);
  transition: var(--transition-base);
  position: relative;
  background: var(--white);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}
.match-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
  border-color: var(--primary-light);
}
.match-avatar {
  width: 60px;
  height: 60px;
  border-radius: var(--radius-full);
  background-color: var(--primary-light);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary);
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: var(--spacing-sm);
}
.name-display {
  font-size: 1.25rem;
  color: var(--primary);
  font-weight: 600;
  margin-bottom: var(--spacing-xs);
}
.compatibility-score {
  font-size: 1rem;
  color: var(--success);
  margin-bottom: var(--spacing-sm);
  background: rgba(52, 168, 83, 0.1);
  padding: 4px 12px;
  border-radius: 20px;
}
.btn-predict {
  background-color: var(--white);
  color: var(--primary);
  border: 1px solid var(--primary);
  border-radius: var(--radius-sm);
  padding: 8px 16px;
  cursor: pointer;
  transition: var(--transition-base);
  font-weight: 500;
}
.btn-predict:hover {
  background-color: var(--primary);
  color: var(--white);
}

/* Prediction Container */
.prediction-container {
  background: var(--white);
  border-radius: var(--radius-sm);
  padding: var(--spacing-md);
  margin-top: var(--spacing-lg);
  text-align: center;
  box-shadow: var(--shadow-sm);
  animation: fadeIn 0.5s ease;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.prediction-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-md);
  margin-bottom: var(--spacing-md);
}
.prediction-avatar {
  width: 50px;
  height: 50px;
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1.2rem;
}
.donor-avatar-sm {
  background-color: var(--primary);
  color: var(--white);
}
.recipient-avatar-sm {
  background-color: var(--primary-light);
  color: var(--primary);
}
.prediction-arrow {
  font-size: 1.5rem;
  color: var(--gray-500);
}
.prediction-success {
  color: var(--success);
  font-size: 1.25rem;
  margin-bottom: var(--spacing-sm);
  font-weight: 600;
  background: rgba(52, 168, 83, 0.1);
  padding: 8px 16px;
  border-radius: 30px;
  display: inline-block;
}
.prediction-failure {
  color: var(--danger);
  font-size: 1.25rem;
  margin-bottom: var(--spacing-sm);
  font-weight: 600;
  background: rgba(234, 67, 53, 0.1);
  padding: 8px 16px;
  border-radius: 30px;
  display: inline-block;
}
.probability-gauge {
  height: 8px;
  background: var(--gray-300);
  border-radius: var(--radius-full);
  margin: var(--spacing-sm) 0;
  overflow: hidden;
  position: relative;
}
.probability-fill {
  height: 100%;
  border-radius: var(--radius-full);
  transition: width 1.5s cubic-bezier(0.22, 1, 0.36, 1);
}
.fill-success {
  background: linear-gradient(90deg, #34a853, #4caf50);
}
.fill-warning {
  background: linear-gradient(90deg, #fbbc05, #ffc107);
}
.fill-danger {
  background: linear-gradient(90deg, #ea4335, #f44336);
}
.probability-labels {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: var(--gray-500);
  margin-top: 5px;
}
.prediction-details {
  background: var(--gray-100);
  border-radius: var(--radius-sm);
  padding: var(--spacing-sm);
  margin-top: var(--spacing-md);
}
.prediction-details h3 {
  margin-bottom: var(--spacing-sm);
  color: var(--gray-900);
}
.prediction-details ul {
  list-style: none;
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--spacing-xs);
}
.prediction-details li {
  padding: 8px;
  background: var(--white);
  border-radius: var(--radius-sm);
  margin-bottom: 5px;
  font-size: 0.9rem;
}

/* Status Lists */
.status-list {
  margin-top: var(--spacing-md);
}
.status-item {
  display: flex;
  align-items: center;
  margin-bottom: var(--spacing-xs);
  padding: var(--spacing-xs);
  border-radius: var(--radius-sm);
  transition: var(--transition-base);
}
.status-item:hover {
  background-color: var(--primary-transparent);
}
.status-indicator {
  width: 10px;
  height: 10px;
  border-radius: var(--radius-full);
  margin-right: var(--spacing-xs);
}
.status-active {
  background-color: var(--success);
}
.status-pending {
  background-color: var(--warning);
}
.timestamp {
  margin-left: auto;
  font-size: 0.85rem;
  color: var(--gray-500);
}

/* Loader */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-xl);
}
.loader {
  border: 5px solid var(--primary-light);
  border-radius: 50%;
  border-top: 5px solid var(--primary);
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
  margin-bottom: var(--spacing-md);
}

/* Alert */
.alert {
  padding: var(--spacing-md);
  border-radius: var(--radius-sm);
  margin: var(--spacing-md) 0;
  position: relative;
}
.alert-info {
  background-color: #e8f4fd;
  border-left: 4px solid #2196f3;
  color: #0c5893;
}

/* Animation keyframes */
@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}



/* Snackbar styles */
#snackbar {
visibility: hidden;
min-width: 250px;
background-color: #111;
color: #fff;
text-align: center;
border-radius: 2px;
padding: 16px;
position: fixed;
z-index: 999;
left: 30px; /* Position from the right */
top: 30px;   /* Position from the top */
font-size: 17px;
}

#snackbar.show {
visibility: visible;
-webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
animation: fadein 0.5s, fadeout 0.5s 2.5s;
}

@-webkit-keyframes fadein {
from {top: 0; opacity: 0;} 
to {top: 30px; opacity: 1;}
}

@keyframes fadein {
from {top: 0; opacity: 0;}
to {top: 30px; opacity: 1;}
}

@-webkit-keyframes fadeout {
from {top: 30px; opacity: 1;} 
to {top: 0; opacity: 0;}
}

@keyframes fadeout {
from {top: 30px; opacity: 1;}
to {top: 0; opacity: 0;}
}


    
    /* Snackbar styles */
    #snackbar {
      visibility: hidden;
      min-width: 250px;
      background-color: #111;
      color: #fff;
      text-align: center;
      border-radius: 2px;
      padding: 16px;
      position: fixed;
      z-index: 999;
      left: 30px;
      top: 30px;
      font-size: 17px;
    }
    #snackbar.show {
      visibility: visible;
      animation: fadein 0.5s, fadeout 0.5s 2.5s;
    }
    @keyframes fadein {
      from { top: 0; opacity: 0; } 
      to { top: 30px; opacity: 1; }
    }
    @keyframes fadeout {
      from { top: 30px; opacity: 1; }
      to { top: 0; opacity: 0; }
    }
    
    /* Matched label style */
    .matched-label {
      font-weight: bold;
      margin-top: 10px;
      color: var(--gray-500);
    }
    
    /* Transplant confirmed label style */
    .transplant-confirmed {
      font-weight: bold;
      margin-top: 10px;
      color: var(--success);
    }

    .logout-button {
    background: none;
    border: none;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: var(--transition-base);
}

.logout-button:hover {
    background: var(--primary-transparent);
}

.logout-button svg {
    width: 18px;
    height: 18px;
}
  </style>
</head>
<body>
  <!-- Snackbar element for feedback -->
  <div id="snackbar"></div>
  
  <!-- Modified Navbar Section -->
<nav class="navbar">
    <div class="container navbar-content">
        <div class="logo">
            <img src="../assets/images/logo-removebg-preview.png" alt="LifeLink Logo">
        </div>
        <div class="user-profile">
            <form method="post" action="../actions/logout.php">
                <button type="submit" name="logout" class="logout-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Logout
                </button>
            </form>
            <span>Welcome, <?php echo $donor['name']; ?></span>
            <div class="user-avatar"><?php echo substr($donor['name'], 0, 1); ?></div>
        </div>
    </div>
</nav>
  <div class="container">
    <?php if (!empty($error)): ?>
      <div class="alert alert-info">
        <?php echo $error; ?>
      </div>
    <?php elseif (!empty($message)): ?>
      <div class="alert alert-info">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>
    <div class="main-container">
      <!-- Card 1: Match Criteria -->


      <!-- Card 2: Donor Profile & Match Finder -->
      <div class="card match-panel">
        <h1>Organ Donor Match Finder</h1>
        <?php if ($donor): ?>
          <div class="donor-profile" id="donorProfile">
            <div class="donor-avatar" id="donorAvatar">
              <?php echo substr($donor['name'], 0, 1); ?>
            </div>
            <h3 id="donorName"><?php echo $donor['name']; ?></h3>
            <div class="donor-details">
              <span class="donor-detail" id="donorBloodType">Blood Type: <?php echo $donor['blood_type']; ?></span>
              <span class="donor-detail" id="donorAge">Age: <?php echo $donor['init_age']; ?></span>
              <span class="donor-detail" id="donorBMI">BMI: <?php echo $donor['bmi_tcr']; ?></span>
              <span class="donor-detail" id="donorOrgan">Organ: <?php echo $donor['organ_type']; ?></span>
            </div>
          </div>
        <?php endif; ?>
        
        <!-- Match Button -->
        <form method="post" action="" id="matchForm">
          <button type="submit" name="find_matches" class="match-button" id="matchButton">
            Find Compatible Match
          </button>
        </form>

        <!-- Matching Results -->
        <?php if ($showMatches && !empty($matches)): ?>
          <div class="matching-container" id="matchingContainer">
            <?php foreach ($matches as $match): ?>
              <div class="match-card">
                <?php 
$pCode = $match['patient_code'] ?? 'Patient #' . $match['id'];
                ?>
                <div class="match-avatar"><?php echo htmlspecialchars(substr($pCode, 0, 1)); ?></div>
                <div class="name-display">
                  <?php echo htmlspecialchars($pCode); ?>
                </div>
                <div class="compatibility-score">
                  <?php echo number_format($match['compatibility_score'], 1); ?>% Match
                </div>
                <?php if (isAlreadyMatched($donor['id'], $match['id'])): ?>
                  <div class="matched-label">Matched</div>
                <?php else: ?>
                  <!-- Confirm Match Form (AJAX) -->
                  <form class="confirm-match-form">
                    <input type="hidden" name="recipient_id" value="<?php echo $match['id']; ?>">
                    <input type="hidden" name="match_score" value="<?php echo $match['compatibility_score']; ?>">
                    <button type="submit" name="confirm_match" class="btn-predict" style="margin-top: 10px;">Confirm Match</button>
                  </form>
                <?php endif; ?>
                <!-- Predict Form -->
                <form method="post" action="">
                  <input type="hidden" name="recipient_id" value="<?php echo $match['id']; ?>">
                  <button type="submit" name="predict_success" class="btn-predict" style="margin-top:10px;">Predict Transplant Success</button>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
        <?php elseif ($showMatches): ?>
          <div class="alert alert-info">
            No compatible recipients found. Please check back later.
          </div>
        <?php endif; ?>

        <!-- Prediction Results -->
        <?php if ($showPrediction && $prediction): ?>
          <div class="prediction-container" id="predictionResultsContainer">
            <div class="prediction-header">
              <div class="prediction-avatar donor-avatar-sm">
                <?php echo substr($donor['name'], 0, 1); ?>
              </div>
              <div class="prediction-arrow">→</div>
              <div class="prediction-avatar recipient-avatar-sm">
                <?php echo isset($selectedMatch['name']) ? substr($selectedMatch['name'], 0, 1) : '?'; ?>
              </div>
            </div>
            <h2>Transplant Success Prediction</h2>
            <h4 class="<?php echo $prediction['is_success'] ? 'prediction-success' : 'prediction-failure'; ?>">
              <?php echo $prediction['prediction']; ?>
            </h4>
            <p>Our model predicts a <?php echo $prediction['probability']; ?>% probability of transplant success.</p>
            <div class="probability-gauge">
              <div class="probability-fill <?php echo $prediction['probability'] > 70 ? 'fill-success' : ($prediction['probability'] > 40 ? 'fill-warning' : 'fill-danger'); ?>" style="width: <?php echo $prediction['probability']; ?>%;"></div>
            </div>
            <div class="prediction-details">
              <h3>Match Details</h3>
              <ul>
                <li>Age: <?php echo $prediction['input_data']['INIT_AGE']; ?></li>
                <li>BMI: <?php echo $prediction['input_data']['BMI_TCR']; ?></li>
                <li>Weight: <?php echo $prediction['input_data']['WGT_KG_TCR']; ?></li>
                <li>Height: <?php echo $prediction['input_data']['HGT_CM_TCR']; ?></li>
              </ul>
            </div>
            <?php
            // Check if a match exists for this donor and recipient
            $stmtMatch = $conn->prepare("SELECT id FROM matches WHERE donor_id = ? AND recipient_id = ?");
            $stmtMatch->bind_param("ii", $donor['id'], $recipient_id);
            $stmtMatch->execute();
            $resultMatch = $stmtMatch->get_result();
            $match_record = $resultMatch->fetch_assoc();
            $stmtMatch->close();
            ?>
            <?php if ($match_record): ?>
              <?php
              // Get recipient's hospital_id through the match
    $stmtHospital = $conn->prepare("SELECT r.hospital_id 
    FROM recipients r
    JOIN matches m ON m.recipient_id = r.id
    WHERE m.id = ?");
$stmtHospital->bind_param("i", $match_record['id']);
$stmtHospital->execute();
$hospitalResult = $stmtHospital->get_result();
$hospitalData = $hospitalResult->fetch_assoc();
$hospital_id = $hospitalData['hospital_id'] ?? null;
$stmtHospital->close();
              // Check if a transplant record already exists for this match
              $stmtTransplant = $conn->prepare("SELECT id FROM transplants WHERE match_id = ?");
              $stmtTransplant->bind_param("i", $match_record['id']);
              $stmtTransplant->execute();
              $resultTransplant = $stmtTransplant->get_result();
              $transplant_record = $resultTransplant->fetch_assoc();
              $stmtTransplant->close();
              ?>
              <?php if ($transplant_record): ?>
                <div class="transplant-confirmed" style="margin-top:20px;">Transplant Confirmed (ID: <?php echo htmlspecialchars($transplant_record['id']); ?>)</div>
              <?php else: ?>
                <!-- Confirm Transplant Form (AJAX) -->
                <form method="post" action="" id="confirmTransplantForm" style="margin-top:20px;">
        <input type="hidden" name="match_id" value="<?php echo $match_record['id']; ?>">
        <input type="hidden" name="hospital_id"
       value="<?= htmlspecialchars($hospital_id ?? '') ?>">
        <input type="hidden" name="status" value="completed">
        <button type="submit" name="confirm_transplant" class="match-button" style="width: auto; padding: 0 20px;">Confirm Transplant</button>
      </form>
              <?php endif; ?>
            <?php else: ?>
              <div class="matched-label" style="margin-top:20px;">Please confirm a match first.</div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Card 3: Active & Recent Matches (Static Example) -->
      <div class="card">
<!-- Replace the entire third card section with this -->
<div class="card">
    <h2>Your Donation Journey</h2>
    
    <!-- Dynamic Stats -->
    <div class="stats-container">
        <?php
        // Get donor stats
        $stats_query = "SELECT 
            (SELECT COUNT(*) FROM matches WHERE donor_id = ?) as total_matches,
            (SELECT COUNT(*) FROM transplants t 
             JOIN matches m ON t.match_id = m.id 
             WHERE m.donor_id = ?) as completed_transplants";
        $stmt = $conn->prepare($stats_query);
        $stmt->bind_param("ii", $donor['id'], $donor['id']);
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        ?>
        
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_matches'] ?? 0 ?></div>
            <div class="stat-label">Total Matches</div>
            <div class="stat-progress">
                <div class="progress-bar" style="width: <?= min(($stats['total_matches'] ?? 0)*20, 100) ?>%"></div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value"><?= $stats['completed_transplants'] ?? 0 ?></div>
            <div class="stat-label">Successful Transplants</div>
            <div class="stat-progress">
                <div class="progress-bar" style="width: <?= min(($stats['completed_transplants'] ?? 0)*33, 100) ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Journey Timeline -->
    <div class="journey-timeline">
        <div class="timeline-item">
            <div class="timeline-icon">🩺</div>
            <div class="timeline-content">
                <h4>Medical Screening</h4>
                <p>Completed on <?= date('M j, Y', strtotime($donor['created_at'])) ?></p>
            </div>
        </div>
        
        <?php if ($stats['total_matches'] > 0): ?>
        <div class="timeline-item">
            <div class="timeline-icon">💞</div>
            <div class="timeline-content">
                <h4>First Match Found</h4>
                <p>Started your matching journey</p>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($stats['completed_transplants'] > 0): ?>
        <div class="timeline-item">
            <div class="timeline-icon">🎉</div>
            <div class="timeline-content">
                <h4>First Successful Transplant</h4>
                <p>Changed someone's life forever</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Overview CTA -->
    <div class="overview-cta">
        <a href="overview.php" class="cta-button">
            View Full Overview
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
        </a>
        <p class="cta-subtext">Track all your matches, transplants, and impact</p>
    </div>
</div>

<style>
/* Add these styles to your existing CSS */
.stat-card {
    background: var(--white);
    padding: var(--spacing-md);
    border-radius: var(--radius-sm);
    margin-bottom: var(--spacing-sm);
    box-shadow: var(--shadow-sm);
}

.stat-progress {
    height: 6px;
    background: var(--gray-300);
    border-radius: var(--radius-full);
    margin-top: var(--spacing-xs);
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: var(--primary);
    transition: width 0.5s ease;
}

.journey-timeline {
    margin-top: var(--spacing-lg);
}

.timeline-item {
    display: flex;
    gap: var(--spacing-md);
    padding: var(--spacing-sm) 0;
    border-bottom: 1px solid var(--gray-300);
}

.timeline-icon {
    font-size: 1.5rem;
    width: 40px;
    text-align: center;
}

.timeline-content h4 {
    color: var(--primary);
    margin-bottom: 4px;
}

.timeline-content p {
    color: var(--gray-500);
    font-size: 0.9rem;
}

.overview-cta {
    text-align: center;
    margin-top: var(--spacing-xl);
    padding: var(--spacing-md);
    background: var(--primary-light);
    border-radius: var(--radius-sm);
}

.cta-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: var(--primary);
    color: var(--white);
    border-radius: var(--radius-sm);
    text-decoration: none;
    transition: var(--transition-base);
}

.cta-button:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
}

.cta-button svg {
    width: 18px;
    height: 18px;
}

.cta-subtext {
    margin-top: var(--spacing-xs);
    color: var(--gray-500);
    font-size: 0.9rem;
}
</style>


    </div>
  </div>
  
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Snackbar function using your snackbar element
      function showSnackbar(message, type = "info") {
          const snackbar = document.getElementById("snackbar");
          if (!snackbar) return;
          
          let backgroundColor = "#111";
          if (type === "success") backgroundColor = "#34a853";
          if (type === "error") backgroundColor = "#ea4335";
          if (type === "info") backgroundColor = "#4070E0";
          if (type === "warning") backgroundColor = "#fbbc05";
          
          snackbar.style.backgroundColor = backgroundColor;
          snackbar.textContent = message;
          snackbar.className = "show";
          
          setTimeout(() => {
              snackbar.className = snackbar.className.replace("show", "");
          }, 3000);
      }
      /* ------------------------------------------------------------------
      Confetti helper – full-screen, multi-burst celebration
------------------------------------------------------------------ */
function triggerConfetti() {
  const duration = 3 * 1000;          // 3 s total
  const animationEnd = Date.now() + duration;
  const defaults = { startVelocity: 25, spread: 360, ticks: 60, zIndex: 9999 };

  function randomInRange(min, max) {
    return Math.random() * (max - min) + min;
  }

  const interval = setInterval(function () {
    const timeLeft = animationEnd - Date.now();

    if (timeLeft <= 0) {
      return clearInterval(interval);
    }

    confetti(Object.assign({}, defaults, {
      particleCount: 50,
      origin: {
        x: randomInRange(0.1, 0.9),
        y: Math.random() - 0.2   // a bit higher
      },
      colors: ['#4070E0', '#34a853', '#fbbc05', '#ea4335']
    }));
  }, 250);
}


      // Attach AJAX event listener for Confirm Match forms
      document.querySelectorAll('.confirm-match-form').forEach(form => {
          form.addEventListener('submit', function(e) {
              e.preventDefault();
              const formData = new FormData(form);
              const data = {};
              formData.forEach((value, key) => {
                  data[key] = value;
              });
              // Add donor_id from PHP
              data.donor_id = <?php echo json_encode($donor['id']); ?>;
              
              const submitButton = form.querySelector('button[type="submit"]');
              const originalButtonText = submitButton.innerHTML;
              submitButton.disabled = true;
              submitButton.innerHTML = 'Processing...';
              
              fetch("/api/confirm_match", {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json'
                  },
                  body: JSON.stringify(data)
              })
              .then(response => response.json())
              .then(result => {
                  if (result.match_id) {
                    triggerConfetti();
Swal.fire({
  icon: 'success',
  title: 'Match confirmed!',
  text : 'Great news – the transplant team will reach out to you soon.',
  confirmButtonColor: '#4070E0'
});                      form.innerHTML = `<div class="btn-predict" style="margin-top: 10px;">Match confirmed (ID: ${result.match_id})</div>`;
                      // Optionally, reload the page to update the prediction section
                      setTimeout(() => {
                          window.location.reload();
                      }, 4000);
                  } else if (result.error) {
                      showSnackbar("Error: " + result.error, "error");
                      submitButton.disabled = false;
                      submitButton.innerHTML = originalButtonText;
                  }
              })
              .catch(err => {
                  console.error("Error:", err);
                  showSnackbar("An error occurred while confirming the match.", "error");
                  submitButton.disabled = false;
                  submitButton.innerHTML = originalButtonText;
              });
          });
      });

      // Attach AJAX event listener for Confirm Transplant form
      const confirmTransplantForm = document.getElementById("confirmTransplantForm");
      if (confirmTransplantForm) {
          confirmTransplantForm.addEventListener("submit", function(e) {
              e.preventDefault();
              const formData = new FormData(confirmTransplantForm);
              const data = {};
              formData.forEach((value, key) => {
                  data[key] = value;
              });
              
              // Check if match_id exists
              if (!data.match_id) {
                  showSnackbar("Error: Missing match ID. Please confirm a match first.", "error");
                  return;
              }
              
              const submitButton = confirmTransplantForm.querySelector('button[type="submit"]');
              const originalButtonText = submitButton.innerHTML;
              submitButton.disabled = true;
              submitButton.innerHTML = 'Processing...';
              
              // Add hospital_id and status if not already in the form data
              data.hospital_id = <?php echo json_encode(isset($_SESSION['hospital_id']) ? $_SESSION['hospital_id'] : null); ?>;
              data.status = "completed";
              
              fetch("/api/confirm_transplant", {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json'
                  },
                  body: JSON.stringify(data)
              })
              .then(response => response.json())
              .then(result => {
                  if (result.transplant_id) {
                      showSnackbar("Transplant confirmed successfully! ID: " + result.transplant_id, "success");
                      confirmTransplantForm.innerHTML = `<div class="match-button" style="background-color: var(--success);">Transplant confirmed (ID: ${result.transplant_id})</div>`;
                  } else if (result.error) {
                      showSnackbar("Error: " + result.error, "error");
                      submitButton.disabled = false;
                      submitButton.innerHTML = originalButtonText;
                  }
              })
              .catch(err => {
                  console.error("Error:", err);
                  showSnackbar("An error occurred while confirming the transplant.", "error");
                  submitButton.disabled = false;
                  submitButton.innerHTML = originalButtonText;
              });
          });
      }
    });

    // Sidebar toggle and dark mode (existing functionality)
    const body = document.querySelector("body"),
          modeToggle = document.querySelector(".mode-toggle"),
          sidebar = document.querySelector("nav"),
          sidebarToggle = document.querySelector(".sidebar-toggle");
    
    let getMode = localStorage.getItem("mode");
    if (getMode && getMode === "dark") {
        body.classList.add("dark");
        modeToggle.querySelector(".switch").style.left = "19px";
    }
    
    modeToggle.addEventListener("click", () => {
        body.classList.toggle("dark");
        localStorage.setItem("mode", body.classList.contains("dark") ? "dark" : "light");
    });
    
    sidebarToggle.addEventListener("click", () => {
        sidebar.classList.toggle("close");
        localStorage.setItem("status", sidebar.classList.contains("close") ? "close" : "open");
    });
  </script>
</body>
</html>
