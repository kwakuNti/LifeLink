<?php
include_once '../config/connection.php';
session_start();
// Check if user is logged in and is a donor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'donor') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get donor information - join users and donors tables
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

// Function to call our Flask API
function callAPI($endpoint, $data = []) {
    $api_url = "http://localhost:5000/api/" . $endpoint;
    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $api_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);
    }
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
    if ($err) {
        return ["error" => "cURL Error: " . $err];
    }
    
    return json_decode($response, true);
}

// Initialize variables for match and prediction handling
$matches = [];
$prediction = null;
$showMatches = false;
$showPrediction = false;
$recipient_id = null;
$selectedMatch = null;

// Handle Find Matches button click
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

// Handle Predict Success button click
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
      grid-template-columns: 300px 1fr 300px;
      gap: var(--spacing-md);
      padding: var(--spacing-md) var(--spacing-sm);
    }
    @media (max-width: 1100px) {
      .main-container {
        grid-template-columns: 250px 1fr;
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
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="container navbar-content">
      <div class="logo">
        <img src="../assets/images/logo-removebg-preview.png" alt="LifeLink Logo">
      </div>
      <div class="user-profile">
        <span id="username">Welcome back</span>
        <div class="user-avatar" id="userInitials">D</div>
      </div>
    </div>
  </nav>
  <div class="container">
    <div class="main-container">
      <!-- Card 1: Match Criteria -->
      <div class="card">
        <h2>Match Criteria</h2>
        <!-- <ul class="criteria-list">
          <li>Age (INIT_AGE)</li>
          <li>BMI (BMI_TCR)</li>
          <li>Days on Waiting List (DAYSWAIT_ALLOC)</li>
          <li>Kidney Cluster</li>
          <li>Diagnosis Code (DGN_TCR)</li>
          <li>Weight (WGT_KG_TCR)</li>
          <li>Height (HGT_CM_TCR)</li>
          <li>Glomerular Filtration Rate (GFR)</li>
          <li>On Dialysis</li>
          <li>ABO Blood Type</li>
        </ul> -->
        <div class="stats-container">
          <div class="stat-box">
            <h3>3,500+</h3>
            <p>Active Recipients</p>
          </div>
          <div class="stat-box">
            <h3>85%</h3>
            <p>Success Rate</p>
          </div>
        </div>
        
        <h2 style="margin-top: var(--spacing-xl);">Matching Process</h2>
        <div class="criteria-list" style="display: block;">
          <li>We evaluate over 10 clinical factors</li>
          <li>Advanced AI predicts compatibility</li>
          <li>Medical team validates all matches</li>
          <li>SLA: 24-48 hours for confirmation</li>
        </div>
      </div>

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

        <!-- Match Button (submits form to trigger find_matches) -->
        <form method="post" action="" id="matchForm">
          <button type="submit" name="find_matches" class="match-button" id="matchButton">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" 
              viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" 
              stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2a8 8 0 0 0-8 8c0 5.4 7 11.5 7.3 11.8a1 1 0 0 0 1.3 0C13 21.5 20 15.4 20 10a8 8 0 0 0-8-8z"/>
            </svg>
            Find Compatible Match
          </button>
        </form>

        <!-- Matching Results -->
        <?php if ($showMatches && !empty($matches)): ?>
          <div class="matching-container" id="matchingContainer">
            <?php foreach ($matches as $match): ?>
              <div class="match-card">
                <div class="match-avatar"><?php echo substr($match['name'], 0, 1); ?></div>
                <div class="name-display">
                  <?php echo isset($match['name']) ? $match['name'] : 'Patient #' . $match['id']; ?>
                </div>
                <div class="compatibility-score">
                  <?php echo number_format($match['compatibility_score'], 1); ?>% Match
                </div>
                <form method="post" action="">
                  <input type="hidden" name="recipient_id" value="<?php echo $match['id']; ?>">
                  <button type="submit" name="predict_success" class="btn-predict">Predict Transplant Success</button>
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
              <div class="probability-fill" style="width: <?php echo $prediction['probability']; ?>%;"></div>
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
          </div>
        <?php endif; ?>
      </div>

      <!-- Card 3: Active & Recent Matches (Static Example) -->
      <div class="card">
        <h2>Active Matches</h2>
        <div class="status-list">
          <div class="status-item">
            <span class="status-indicator status-active"></span>
            <span>John D. → Sarah M.</span>
            <span class="timestamp">In Progress</span>
          </div>
          <div class="status-item">
            <span class="status-indicator status-pending"></span>
            <span>Michael R. → David K.</span>
            <span class="timestamp">Pending</span>
          </div>
          <div class="status-item">
            <span class="status-indicator status-active"></span>
            <span>Lisa T. → James W.</span>
            <span class="timestamp">In Progress</span>
          </div>
        </div>

        <h2 style="margin-top: var(--spacing-xl);">Recent Successful Matches</h2>
        <div class="status-list">
          <div class="status-item">
            <span>Emily C. → Robert M.</span>
            <span class="timestamp">2 days ago</span>
          </div>
          <div class="status-item">
            <span>Alex P. → Maria S.</span>
            <span class="timestamp">5 days ago</span>
          </div>
          <div class="status-item">
            <span>Thomas H. → Karen L.</span>
            <span class="timestamp">1 week ago</span>
          </div>
        </div>

        <h2 style="margin-top: var(--spacing-xl);">Statistics</h2>
        <div class="stats-container" style="grid-template-columns: 1fr;">
          <div class="stat-box">
            <h3>93.2%</h3>
            <p>Avg. Match Accuracy</p>
          </div>
          <div class="stat-box">
            <h3>18 Days</h3>
            <p>Avg. Match Time</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <script>
    // Additional JavaScript interactions can be added here if needed.
    // Since the backend now handles match and prediction submissions,
    // no simulated JavaScript functions are used in place of PHP.
  </script>
</body>
</html>
