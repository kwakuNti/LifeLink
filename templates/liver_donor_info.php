<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: ../templates/login.php?status=error&message=Please+log+in+first.");
  exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
  <link rel="manifest" href="../favicon_io/site.webmanifest">
  <title>Liver Donor Information Form</title>
  <link rel="stylesheet" href="../public/css/snackbar.css">
  <!-- Use your desired font -->
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap"
    rel="stylesheet"
  />

  <style>
    /* Base Reset & Typography */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body, html {
      height: 100%;
      font-family: 'Poppins', sans-serif;
      background-color: rgba(240, 240, 240, 0.78);
    }

    /* Header with Logo */
    header {
      padding: 0rem;
      text-align: center;
    }
    header .logo img {
      max-width: 300px;
      width: 100%;
      height: auto;
    }

    /* Main Container */
    .container {
      max-width: 1000px; /* increase width to reduce scrolling */
      margin: 0 auto;
      padding: 0rem;
      border-radius: 10px;
    }
    .page-title {
      text-align: center;
      font-size: 2rem;
      margin-bottom: 1.5rem;
      color: #1e3a8a;
      font-weight: 700;
    }

    /* Form Layout */
    .form-row {
      display: flex;
      flex-wrap: wrap; /* so it wraps on small screens */
      gap: 2rem;       /* spacing between columns */
      margin-bottom: 1rem;
    }
    .form-col {
      flex: 1;          /* occupy equal space */
      min-width: 300px; /* prevent columns from shrinking too much */
    }
    .form-col h3 {
      margin-bottom: 1rem;
      color: #1e3a8a;
      font-size: 1.2rem;
      font-weight: 600;
    }

    /* Form Fields */
    .form-group {
      margin-bottom: 1rem;
      position: relative; /* for tooltips */
    }
    .form-group label {
      display: inline-block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #333;
    }
    .form-group input,
    .form-group select {
      width: 100%;
      padding: 0.75rem;
      font-size: 1rem;
      border: 1px solid #ddd;
      border-radius: 5px;
    }
    small.text-muted {
      display: block;
      margin-top: 0.25rem;
      font-size: 0.85rem;
      color: #777;
    }

    /* Info Icon / Tooltip */
    .info-icon {
      display: inline-block;
      margin-left: 6px;
      cursor: pointer;
      color: #777;
      font-weight: bold;
      border: 1px solid #ccc;
      border-radius: 50%;
      width: 18px;
      height: 18px;
      text-align: center;
      font-size: 0.8rem;
      line-height: 16px;
    }
    .tooltip-text {
      visibility: hidden;
      opacity: 0;
      width: 250px;
      background-color: #1e3a8a;
      color: #fff;
      text-align: left;
      border-radius: 5px;
      padding: 0.5rem;
      position: absolute;
      z-index: 999;
      transform: translateY(-5px);
      transition: opacity 0.3s;
      font-size: 0.85rem;
      top: -80px;
      left: 0;
    }
    .info-icon:hover + .tooltip-text {
      visibility: visible;
      opacity: 1;
      transform: translateY(0);
    }

    /* Submit Button */
    .submit-container {
      text-align: center;
      margin-top: 2rem;
    }
    .btn {
      padding: 0.75rem 1.5rem;
      font-size: 1rem;
      background-color: #1e3a8a;
      color: #fff;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .btn:hover {
      background-color: #153e7e;
    }

    /* Custom Modal */
    .modal-overlay {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background-color: rgba(0, 0, 0, 0.6);
      display: none; /* hidden by default */
      justify-content: center;
      align-items: center;
      z-index: 999;
    }
    .modal-overlay.active {
      display: flex; /* show the overlay */
    }
    .modal-content {
      background-color: #fff;
      width: 80%;
      max-width: 700px;
      padding: 2rem;
      border-radius: 10px;
      position: relative;
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }
    .modal-header h5 {
      font-size: 1.4rem;
      color: #1e3a8a;
    }
    .close-modal {
      cursor: pointer;
      font-size: 1.2rem;
      background: none;
      border: none;
    }
    .modal-form-row {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
    }
    .modal-form-col {
      flex: 1;
      min-width: 250px;
    }
    #clusterResult {
      margin-top: 1rem;
      background: #eef5ff;
      padding: 1rem;
      border-radius: 5px;
      display: none; /* hidden until we "find" the cluster */
    }
    #clusterValue {
      font-weight: bold;
      color: #1e3a8a;
    }
    .cluster-badge {
  font-size: 1.5rem;
  font-weight: bold;
  color: #1e3a8a;
  padding: 0.5rem 1rem;
  border: 2px solid #1e3a8a;
  border-radius: 5px;
  display: inline-block;
  margin: 0.5rem 0;
}
    .alert-info {
      background-color: #dbeafe;
      border: 1px solid #bfdbfe;
      border-radius: 5px;
      padding: 1rem;
      color: #0c4a6e;
    }

    /* Link styling (for small inline links) */
    a.inline-link {
      color: #1e3a8a;
      text-decoration: none;
    }
    a.inline-link:hover {
      text-decoration: underline;
    }
    
    /* Video tooltips */
    .video-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0,0,0,0.7);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }
    .video-container {
      background: white;
      padding: 20px;
      border-radius: 10px;
      width: 80%;
      max-width: 800px;
    }
    .video-container iframe {
      width: 100%;
      height: 400px;
    }
    .video-title {
      margin-bottom: 15px;
      color: #1e3a8a;
    }
    .close-video {
      float: right;
      font-size: 24px;
      cursor: pointer;
    }
    .confirmation-modal {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0,0,0,0.7);
      z-index: 1000;
      display: none;
      align-items: center;
      justify-content: center;
    }
    
    .confirmation-content {
      background: white;
      padding: 30px;
      border-radius: 10px;
      width: 80%;
      max-width: 600px;
      text-align: center;
    }
    
    .confirmation-title {
      color: #1e3a8a;
      margin-bottom: 20px;
      font-size: 1.5rem;
    }
    
    .confirmation-message {
      margin-bottom: 25px;
      color: #333;
      line-height: 1.5;
    }
    
    .confirmation-buttons {
      display: flex;
      justify-content: center;
      gap: 20px;
    }
    
    .btn-confirm {
      background-color: #1e3a8a;
    }
    
    .btn-cancel {
      background-color: #6c757d;
    }
    
    /* Loading indicator */
    /* Loading indicator */
/* Loading Snackbar Styles */
.loading-snackbar {
  visibility: hidden;
  min-width: 300px;
  background-color: #111;
  color: #fff;
  text-align: center;
  border-radius: 4px;
  padding: 16px 24px;
  position: fixed;
  z-index: 1000;
  left: 15%;
  transform: translateX(-50%);
  top: 30px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.loading-snackbar.show {
  visibility: visible;
  animation: fadein 0.5s;
}

/* Progress Bar Styles */
.progress-container {
  flex-grow: 1;
  height: 4px;
  background-color: rgba(235, 218, 218, 0.94);
  border-radius: 2px;
  margin: 0 15px;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  width: 0;
  background-color: white;
  border-radius: 2px;
  transition: width 5s linear;
}

/* Cancel Button */
.cancel-btn {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  font-size: 1.2rem;
  padding: 0;
  margin: 0;
  margin-left: 10px;
}

@keyframes progress {
  0% { width: 0%; }
  100% { width: 100%; }
}
    /* Snackbar styles if not imported */
    #snackbar.show {
      visibility: visible;
      -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
      animation: fadein 0.5s, fadeout 0.5s 2.5s;
    }
    
    #snackbar.error {
      background-color: #111;
    }
    
    #snackbar.success {
      background-color: #111;
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
  </style>
</head>

<body>
  <!-- Header with Logo -->
  <header>
    <div class="logo">
      <img src="../assets/images/logo-removebg-preview.png" alt="LifeLink Logo">
    </div>
  </header>

  <div class="container">
    <h2 class="page-title">Liver Donor Information</h2>
    
    <!-- Main Form -->
    <form id="donorForm" action="../actions/liver_donor_medical_info_action.php" method="post">
    <div class="form-row">
        <!-- Left Column -->
        <div class="form-col">
          <h3>Personal Information</h3>
          
          <!-- Age -->
          <div class="form-group">
            <label for="init_age">Age:</label>
            <input
              type="number"
              id="init_age"
              name="init_age"
              required
              min="18"
              max="90"
              step="1"
              value="50"
            />
          </div>

          <!-- Blood Type -->
          <div class="form-group">
            <label for="blood_type_main">Blood Type:</label>
            <select id="blood_type_main" name="blood_type" required>
              <option value="" disabled selected>Select your blood type</option>
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="AB">AB</option>
              <option value="O">O</option>
            </select>
            <small class="text-muted">
              <a href="#" class="inline-link learn-more" data-video="blood-type">
                What is blood type and why does it matter?
              </a>
            </small>
          </div>

          <!-- Height -->
          <div class="form-group">
            <label for="hgt_cm_tcr">Height (cm):</label>
            <input
              type="number"
              id="hgt_cm_tcr"
              name="hgt_cm_tcr"
              min="100"
              max="220"
              step="1"
              required
              placeholder="e.g., 175"
            />
          </div>

          <!-- Weight -->
          <div class="form-group">
            <label for="wgt_kg_tcr">Weight (kg):</label>
            <input
              type="number"
              id="wgt_kg_tcr"
              name="wgt_kg_tcr"
              min="30"
              max="200"
              step="0.1"
              required
              placeholder="e.g., 70.5"
            />
          </div>
          
          <!-- BMI with tooltip icon + auto-calculation -->
          <div class="form-group">
            <label for="bmi_tcr">
              BMI (Auto-calculated):
              <span class="info-icon" title="Body Mass Index">?</span>
              <span class="tooltip-text">
                BMI stands for Body Mass Index. It's calculated using your height and weight and is a simple 
                measure of body fat. The normal range is 18.5-24.9. Having a BMI in this range may improve 
                transplant outcomes.
              </span>
            </label>
            <input
              type="number"
              id="bmi_tcr"
              name="bmi_tcr"
              required
              min="15"
              max="45"
              step="0.1"
              readonly
            />
            <small class="text-muted">
              <a href="#" class="inline-link learn-more" data-video="bmi">
                Learn more about BMI and its importance
              </a>
            </small>
          </div>
        </div>

        <!-- Right Column -->
        <div class="form-col">
          <h3>Medical Information</h3>

          <!-- On Dialysis -->
          <div class="form-group">
            <label for="on_dialysis">
              Currently on Dialysis:
              <span class="info-icon" title="Dialysis Status">?</span>
              <span class="tooltip-text">
                Dialysis is a treatment that filters waste from your blood when your Liver can no longer do this.
                Your dialysis status helps determine your medical priority.
              </span>
            </label>
            <select id="on_dialysis" name="on_dialysis" required>
              <option value="N" selected>No</option>
              <option value="Y">Yes</option>
            </select>
            <small class="text-muted">
              <a href="#" class="inline-link learn-more" data-video="dialysis">
                What is dialysis and why does it matter?
              </a>
            </small>
          </div>

          <!-- GFR -->
          <div class="form-group">
            <label for="gfr">
              GFR (Glomerular Filtration Rate):
              <span class="info-icon" title="Kidney Function">?</span>
              <span class="tooltip-text">
                GFR measures how well your kidneys filter blood. Normal GFR is 90-120, while below 60 indicates 
                reduced kidney function. This value helps determine your kidney health status.
              </span>
            </label>
            <input
              type="number"
              id="gfr"
              name="gfr"
              min="5"
              max="120"
              step="1"
              placeholder="e.g., 90"
            />
            <small class="text-muted">
              Ask your doctor for your latest GFR value
            </small>
          </div>

          <!-- Kidney Cluster with tooltip icon -->
          <div class="form-group">
            <label for="kidney_cluster">
              Liver Cluster:
              <span class="info-icon" title="Kidney Clusters">?</span>
              <span class="tooltip-text">
                A "Liver Cluster" groups donors with similar medical characteristics.
                This helps match you with compatible recipients. If you're unsure, 
                use the "Find my cluster" tool below.
              </span>
            </label>
            <select
              id="kidney_cluster"
              name="kidney_cluster"
              required
            >
              <option value="0">Cluster 0</option>
              <option value="1">Cluster 1</option>
            </select>
            <small class="text-muted">
              Not sure which cluster?
              <a href="#" id="openClusterModal" class="inline-link">
                Find your cluster
              </a>
            </small>
          </div>

          <!-- Diagnosis Code (hidden) -->
          <div class="form-group" style="display: none;">
            <label for="dgn_tcr">Diagnosis Code:</label>
            <input
              type="number"
              id="dgn_tcr"
              name="dgn_tcr"
              step="0.01"
              value="0"
            />
          </div>
          
          <!-- Days on waiting list (hidden) -->
          <input type="hidden" id="dayswait_alloc" name="dayswait_alloc" value="0">

        </div>
      </div>

      <!-- Submit -->
      <div class="submit-container">
      <button type="button" id="openConfirmationBtn" class="btn">
      Submit Donor Medical Information
        </button>
      </div>
    </form>
  </div>

  <!-- Modal Overlay for Cluster Detection -->
  <div class="modal-overlay" id="clusterModal">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Find Your liver Cluster</h5>
        <button class="close-modal" id="closeClusterModal">&times;</button>
      </div>

      <form id="clusterForm" action="/find_cluster" method="post">
        <div class="modal-form-row">
          <!-- Column 1 -->
          <div class="modal-form-col">
            <div class="form-group">
              <label for="init_age_cluster">Age:</label>
              <input
                type="number"
                id="init_age_cluster"
                name="init_age"
                required
                min="18"
                max="90"
                step="1"
                value="50"
              />
            </div>

            <div class="form-group">
              <label for="hgt_cm_tcr_cluster">Height (cm):</label>
              <input
                type="number"
                id="hgt_cm_tcr_cluster"
                name="hgt_cm_tcr"
                min="100"
                max="220"
                step="1"
                required
                placeholder="e.g., 175"
              />
            </div>

            <div class="form-group">
              <label for="wgt_kg_tcr_cluster">Weight (kg):</label>
              <input
                type="number"
                id="wgt_kg_tcr_cluster"
                name="wgt_kg_tcr"
                min="30"
                max="200"
                step="0.1"
                required
                placeholder="e.g., 70.5"
              />
            </div>

            <div class="form-group">
              <label for="bmi_tcr_cluster">BMI (Auto-calculated):</label>
              <input
                type="number"
                id="bmi_tcr_cluster"
                name="bmi_tcr"
                required
                min="15"
                max="45"
                step="0.1"
                readonly
              />
            </div>
          </div>

          <!-- Column 2 -->
          <div class="modal-form-col">
            <div class="form-group">
              <label for="gfr_cluster">GFR:</label>
              <input
                type="number"
                id="gfr_cluster"
                name="gfr"
                min="5"
                max="120"
                step="1"
                placeholder="Ask your doctor for this value"
              />
            </div>
            
            <div class="form-group">
              <label for="on_dialysis_cluster">On Dialysis:</label>
              <select
                id="on_dialysis_cluster"
                name="on_dialysis"
              >
                <option value="N" selected>No</option>
                <option value="Y">Yes</option>
              </select>
            </div>

            <div class="form-group">
              <label for="blood_type_cluster">Blood Type:</label>
              <select id="blood_type_cluster" name="blood_type" required>
                <option value="" disabled selected>Select your blood type</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="AB">AB</option>
                <option value="O">O</option>
              </select>
            </div>
          </div>
        </div>

        <div class="submit-container" style="margin-top:1rem;">
          <button type="submit" class="btn">Find My Cluster</button>
        </div>
      </form>

      <!-- Cluster Result -->
      <div id="clusterResult">
  <div class="alert alert-info">
    <h4>Your Liver Cluster: <span id="clusterValue" class="cluster-badge">-</span></h4>
    <p>
      This cluster represents donors with similar liver characteristics.
      Cluster 0: Lower risk profiles<br>
      Cluster 1: Higher risk profiles
    </p>
    <div class="submit-container">
      <button type="button" class="btn" id="useClusterBtn">
        Use This Cluster
      </button>
    </div>
  </div>
</div>
    </div>
  </div>
  <div id="clusterError" style="color: red; display: none; margin-top: 1rem;"></div>
  <!-- Video modals for educational content -->
  <div class="video-modal" id="videoModal">
    <div class="video-container">
      <span class="close-video">&times;</span>
      <h3 class="video-title" id="videoTitle">Understanding BMI</h3>
      <iframe id="videoFrame" src="" frameborder="0" allowfullscreen></iframe>
    </div>
  </div>
  <!-- New confirmation modal -->
  <div class="confirmation-modal" id="confirmationModal">
    <div class="confirmation-content">
      <h3 class="confirmation-title">Important Notice</h3>
      <p class="confirmation-message">
        <strong>Please read carefully:</strong> Once submitted to the blockchain, this information 
        <strong>cannot be modified</strong>. Please verify all entries are accurate before proceeding.
      </p>
      <div class="confirmation-buttons">
        <button type="button" class="btn btn-cancel" id="cancelSubmission">Cancel</button>
        <button type="button" class="btn btn-confirm" id="confirmSubmission">I Confirm All Information is Correct</button>
      </div>
    </div>
  </div>
  <div id="loadingSnackbar" class="loading-snackbar">
  <span>Submitting to blockchain...</span>
  <div class="progress-container">
    <div class="progress-bar" id="progressBar"></div>
  </div>
  <button class="cancel-btn" id="cancelProgress">&times;</button>
</div>

  
  <div id="snackbar"></div>

  <!-- JavaScript -->
  <script>
    // Call the checkForMessage function when the page loads
    document.addEventListener('DOMContentLoaded', function() {
      checkForMessage();
      
      // Initialize BMI calculation if height and weight are already filled
      updateBMI();
    });
    
    // Basic show/hide for the cluster modal
    const openClusterModalLink = document.getElementById('openClusterModal');
    const closeClusterModalBtn = document.getElementById('closeClusterModal');
    const clusterModal = document.getElementById('clusterModal');

    openClusterModalLink.addEventListener('click', (e) => {
      e.preventDefault();
      clusterModal.classList.add('active');
      
      // Copy main form values to cluster form
      document.getElementById('init_age_cluster').value = document.getElementById('init_age').value;
      document.getElementById('hgt_cm_tcr_cluster').value = document.getElementById('hgt_cm_tcr').value;
      document.getElementById('wgt_kg_tcr_cluster').value = document.getElementById('wgt_kg_tcr').value;
      document.getElementById('bmi_tcr_cluster').value = document.getElementById('bmi_tcr').value;
      document.getElementById('gfr_cluster').value = document.getElementById('gfr').value;
      document.getElementById('on_dialysis_cluster').value = document.getElementById('on_dialysis').value;
      document.getElementById('blood_type_cluster').value = document.getElementById('blood_type_main').value;
    });

    closeClusterModalBtn.addEventListener('click', () => {
      clusterModal.classList.remove('active');
    });

    // Mock cluster form submission to demonstrate usage
    const clusterForm = document.getElementById('clusterForm');
    const clusterResultDiv = document.getElementById('clusterResult');
    const clusterValueSpan = document.getElementById('clusterValue');
    const useClusterBtn = document.getElementById('useClusterBtn');

    clusterForm.addEventListener('submit', function (e) {
  e.preventDefault();
  
  const formData = {
    init_age: document.getElementById('init_age_cluster').value,
    hgt_cm_tcr: document.getElementById('hgt_cm_tcr_cluster').value,
    wgt_kg_tcr: document.getElementById('wgt_kg_tcr_cluster').value,
    bmi_tcr: document.getElementById('bmi_tcr_cluster').value,
    gfr: document.getElementById('gfr_cluster').value,
    on_dialysis: document.getElementById('on_dialysis_cluster').value,
    blood_type: document.getElementById('blood_type_cluster').value
  };

  fetch('/api/determine_kidney_cluster', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(formData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.error) {
      showSnackbar('Error determining cluster: ' + data.error, 'error');
      return;
    }
    clusterValueSpan.textContent = data.cluster;
    clusterResultDiv.style.display = 'block';
  })
  .catch(error => {
    showSnackbar('Failed to connect to server', 'error');
  });
});

    // Insert chosen cluster into main form
// Update the use cluster button
useClusterBtn.addEventListener('click', () => {
  const chosenCluster = clusterValueSpan.textContent;
  if (!chosenCluster || isNaN(chosenCluster)) {
    showSnackbar('Invalid cluster detected', 'error');
    return;
  }
  
  // Update main form
  document.getElementById('kidney_cluster').value = chosenCluster;
  clusterModal.classList.remove('active');
  
  // Also update any hidden fields if needed
  document.querySelectorAll('[data-cluster-field]').forEach(field => {
    field.value = chosenCluster;
  });
});

    // Auto-calculate BMI when height or weight changes - in main form
    const heightInput = document.getElementById("hgt_cm_tcr");
    const weightInput = document.getElementById("wgt_kg_tcr");
    const bmiInput = document.getElementById("bmi_tcr");

    function updateBMI() {
      const height = heightInput.value;
      const weight = weightInput.value;
      
      if (height && weight && height > 0) {
        const heightInMeters = height / 100;
        const bmi = (weight / (heightInMeters * heightInMeters)).toFixed(1);
        bmiInput.value = bmi;
      }
    }
    
    if (heightInput && weightInput) {
      heightInput.addEventListener("input", updateBMI);
      weightInput.addEventListener("input", updateBMI);
    }
    
    // Auto-calculate BMI in cluster form
    const heightInputCluster = document.getElementById("hgt_cm_tcr_cluster");
    const weightInputCluster = document.getElementById("wgt_kg_tcr_cluster");
    const bmiInputCluster = document.getElementById("bmi_tcr_cluster");

    function updateClusterBMI() {
      const height = heightInputCluster.value;
      const weight = weightInputCluster.value;
      
      if (height && weight && height > 0) {
        const heightInMeters = height / 100;
        const bmi = (weight / (heightInMeters * heightInMeters)).toFixed(1);
        bmiInputCluster.value = bmi;
      }
    }
    
    if (heightInputCluster && weightInputCluster) {
      heightInputCluster.addEventListener("input", updateClusterBMI);
      weightInputCluster.addEventListener("input", updateClusterBMI);
    }
    
    // Close the cluster modal when clicking outside of it
    window.addEventListener('click', (e) => {
      if (e.target === clusterModal) {
        clusterModal.classList.remove('active');
      }
      if (e.target === document.getElementById('videoModal')) {
        document.getElementById('videoModal').style.display = 'none';
        document.getElementById('videoFrame').src = '';
      }
    });
    
    // Function to check for message in URL parameters
    function checkForMessage() {
      const params = new URLSearchParams(window.location.search);
      if (params.has('status') && params.has('message')) {
        const message = params.get('message');
        const status = params.get('status');
        showSnackbar(message, status);
      }
    }

    // Snackbar display function
    function showSnackbar(message, type) {
      let snackbar = document.getElementById("snackbar");
      snackbar.innerHTML = message;
      snackbar.className = "show " + type;
      setTimeout(() => {
        snackbar.className = snackbar.className.replace("show", "");
      }, 3000);
    }
    
    // Video modal functionality
    const videoModal = document.getElementById('videoModal');
    const videoFrame = document.getElementById('videoFrame');
    const videoTitle = document.getElementById('videoTitle');
    const closeVideo = document.querySelector('.close-video');
    
    // Define video data
    const videos = {
      'bmi': {
        title: 'Understanding BMI for Organ Donors',
        src: 'https://www.youtube.com/embed/_m9At9ywh3E'
      },
      'blood-type': {
        title: 'Blood Type Compatibility in Organ Donation',
        src: 'https://www.youtube.com/embed/7YhAuUZIoYo'
      },
      'dialysis': {
        title: 'What is Dialysis?',
        src: 'https://www.youtube.com/embed/eoKUuwJnkBo'
      }
    };
    
    // Set up learn more links
    document.querySelectorAll('.learn-more').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const videoKey = e.target.dataset.video;
        const video = videos[videoKey];
        
        if (video) {
          videoTitle.textContent = video.title;
          videoFrame.src = video.src;
          videoModal.style.display = 'flex';
        }
      });
    });
    
    // Close video modal
    closeVideo.addEventListener('click', () => {
      videoModal.style.display = 'none';
      videoFrame.src = '';
    });
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
      const height = document.getElementById('hgt_cm_tcr').value;
      const weight = document.getElementById('wgt_kg_tcr').value;
      const bloodType = document.getElementById('blood_type_main').value;
      
      if (!height || !weight || !bloodType) {
        e.preventDefault();
        showSnackbar('Please fill out all required fields', 'error');
      }
    });

 // New JavaScript for confirmation modal and loading indicator
 document.addEventListener('DOMContentLoaded', function() {
      const openConfirmationBtn = document.getElementById('openConfirmationBtn');
      const confirmationModal = document.getElementById('confirmationModal');
      const cancelSubmissionBtn = document.getElementById('cancelSubmission');
      const confirmSubmissionBtn = document.getElementById('confirmSubmission');
      const donorForm = document.getElementById('donorForm');
      
      // Show confirmation modal when submit button is clicked
      openConfirmationBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // First do validation
        const height = document.getElementById('hgt_cm_tcr').value;
        const weight = document.getElementById('wgt_kg_tcr').value;
        const bloodType = document.getElementById('blood_type_main').value;
        
        if (!height || !weight || !bloodType) {
          showSnackbar('Please fill out all required fields', 'error');
          return;
        }
        
        // Show confirmation modal
        confirmationModal.style.display = 'flex';
      });
      
      // Cancel submission
      cancelSubmissionBtn.addEventListener('click', function() {
        confirmationModal.style.display = 'none';
      });
      
      // Confirm submission
      confirmSubmissionBtn.addEventListener('click', function() {
        confirmationModal.style.display = 'none';
        
        // Show loading snackbar with progress bar
        showLoadingSnackbar();
        
        // Submit form after delay unless canceled
        setTimeout(function() {
          if (!window.submissionCanceled) {
            donorForm.submit();
          }
        }, 5000); // 5 second delay
      });
      
      // Close the confirmation modal when clicking outside
      window.addEventListener('click', function(e) {
        if (e.target === confirmationModal) {
          confirmationModal.style.display = 'none';
        }
      });
    });
    
// Loading snackbar with progress bar
// Loading snackbar with progress bar
function showLoadingSnackbar() {
  const loadingSnackbar = document.getElementById("loadingSnackbar");
  const progressBar = document.getElementById("progressBar");
  const cancelBtn = document.getElementById("cancelProgress");
  
  // Reset state
  window.submissionCanceled = false;
  progressBar.style.width = '0';
  progressBar.style.transition = 'none';
  
  // Force reflow to reset animation
  void progressBar.offsetWidth;
  
  // Show snackbar
  loadingSnackbar.className = "loading-snackbar show";
  
  // Start progress animation
  progressBar.style.transition = 'width 5s linear';
  progressBar.style.width = '100%';
  
  // Cancel button handler
  cancelBtn.onclick = function() {
    window.submissionCanceled = true;
    loadingSnackbar.className = "loading-snackbar";
    showSnackbar('Submission canceled', 'error');
  };
  
  // Auto-hide after 5 seconds
  setTimeout(() => {
    if (loadingSnackbar.className.includes('show') && !window.submissionCanceled) {
      loadingSnackbar.className = "loading-snackbar";
      // Form will submit automatically
    }
  }, 5000);
 // this gives time for the DOM to update

      
      // Set up cancel button
      document.getElementById('cancelProgress').addEventListener('click', function() {
        window.submissionCanceled = true;
        snackbar.className = snackbar.className.replace("show", "");
        showSnackbar('Submission canceled', 'error');
      });
      
      // Clear the snackbar after 5 seconds unless manually cleared
      setTimeout(() => {
        if (snackbar.className.includes('show')) {
          snackbar.className = snackbar.className.replace("show", "");
        }
      }, 5000);
    }
    
    // Original snackbar function with added type parameter
    function showSnackbar(message, type) {
  let snackbar = document.getElementById("snackbar");
  snackbar.innerHTML = message;
  snackbar.className = "show " + type;
  setTimeout(() => {
    snackbar.className = snackbar.className.replace("show", "");
  }, 3000);
}
  </script>
</body>
</html>