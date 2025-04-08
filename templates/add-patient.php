<?php
// For example, retrieve patient_id from GET (adjust as needed)
$patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
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
  <title>Add Patient</title>
  <link rel="stylesheet" href="../public/css/snackbar.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
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
      padding: 1rem;
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
      padding: 2rem;
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
      width: 200px;
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
      top: -50px;
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
  </style>
</head>
<body>
  <div class="container">
    <h2 class="page-title">Add Patient Medical Information</h2>
    <form action="../actions/add_patient_action.php" method="POST" onsubmit="return validatePatientForm()">
      <!-- Hidden field for patient_id -->
      <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($patient_id); ?>">
      
      <!-- Two-column layout for the form -->
      <div class="form-row">
        <!-- Left Column -->
        <div class="form-col">
          <div class="form-group">
            <label for="init_age">Age:</label>
            <input type="number" id="init_age" name="init_age" required min="18" max="90" value="50">
          </div>
          <div class="form-group">
            <label for="bmi_tcr">BMI:</label>
            <input type="number" id="bmi_tcr" name="bmi_tcr" required min="15" max="45" step="0.1" value="25">
          </div>
          <div class="form-group">
            <label for="dayswait_alloc">Days on Waiting List:</label>
            <input type="number" id="dayswait_alloc" name="dayswait_alloc" required min="0" value="180">
          </div>
          <div class="form-group">
            <label for="kidney_cluster">Kidney Cluster:</label>
            <select id="kidney_cluster" name="kidney_cluster" required>
              <option value="0">Cluster 0</option>
              <option value="1">Cluster 1</option>
            </select>
            <small class="text-muted">Not sure which cluster? <a href="#" id="openClusterModal" class="inline-link">Find your cluster</a></small>
          </div>
          <div class="form-group">
            <label for="dgn_tcr">Diagnosis Code:</label>
            <input type="number" id="dgn_tcr" name="dgn_tcr" step="0.01">
            <small class="text-muted">Leave blank if unknown</small>
          </div>
        </div>
        
        <!-- Right Column -->
        <div class="form-col">
          <div class="form-group">
            <label for="hgt_cm_tcr">Height (cm):</label>
            <input type="number" id="hgt_cm_tcr" name="hgt_cm_tcr" required min="100" max="220">
          </div>
          <div class="form-group">
            <label for="wgt_kg_tcr">Weight (kg):</label>
            <input type="number" id="wgt_kg_tcr" name="wgt_kg_tcr" required min="30" max="200" step="0.1">
          </div>
          <div class="form-group">
            <label for="gfr">Glomerular Filtration Rate (GFR):</label>
            <input type="number" id="gfr" name="gfr" required min="5" max="120" step="1" value="45">
          </div>
          <div class="form-group">
            <label for="on_dialysis">On Dialysis:</label>
            <select id="on_dialysis" name="on_dialysis">
              <option value="Y">Yes</option>
              <option value="N" selected>No</option>
            </select>
          </div>
          <div class="form-group">
            <label for="blood_type">Blood Type:</label>
            <select id="blood_type" name="blood_type">
              <option value="">Unknown</option>
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="AB">AB</option>
              <option value="O">O</option>
            </select>
          </div>
        </div>
      </div>
      
      <!-- Submit Button -->
      <div class="submit-container">
        <button type="submit" class="btn">Submit Patient Information</button>
      </div>
    </form>
    <div id="snackbar"></div>
  </div>

  <!-- Optional: Cluster Modal if needed (same as donor) -->
  <div class="modal-overlay" id="clusterModal">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Find Your Kidney Cluster</h5>
        <button class="close-modal" id="closeClusterModal">&times;</button>
      </div>
      <form id="clusterForm">
        <div class="modal-form-row">
          <div class="modal-form-col">
            <div class="form-group">
              <label for="init_age_cluster">Age:</label>
              <input type="number" id="init_age_cluster" name="init_age" required min="18" max="90" value="50">
            </div>
            <div class="form-group">
              <label for="bmi_tcr_cluster">BMI:</label>
              <input type="number" id="bmi_tcr_cluster" name="bmi_tcr" required min="15" max="45" step="0.1" value="25">
            </div>
            <div class="form-group">
              <label for="dayswait_alloc_cluster">Days on Waiting List:</label>
              <input type="number" id="dayswait_alloc_cluster" name="dayswait_alloc" required min="0" value="180">
            </div>
          </div>
          <div class="modal-form-col">
            <div class="form-group">
              <label for="gfr_cluster">GFR:</label>
              <input type="number" id="gfr_cluster" name="gfr" min="5" max="120" step="1" value="45">
            </div>
            <div class="form-group">
              <label for="on_dialysis_cluster">On Dialysis:</label>
              <select id="on_dialysis_cluster" name="on_dialysis">
                <option value="Y">Yes</option>
                <option value="N" selected>No</option>
              </select>
            </div>
            <div class="form-group">
              <label for="blood_type_cluster">Blood Type:</label>
              <select id="blood_type_cluster" name="blood_type">
                <option value="">Unknown</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="AB">AB</option>
                <option value="O">O</option>
              </select>
            </div>
          </div>
        </div>
        <div class="submit-container" style="margin-top:1rem;">
          <button type="button" class="btn" id="useClusterBtn">Use This Cluster</button>
        </div>
      </form>
      <div id="clusterResult" style="display:none;">
        <div class="alert alert-info">
          <h4>Your Kidney Cluster: <span id="clusterValue">-</span></h4>
          <p>This cluster represents a group of patients with similar kidney characteristics.</p>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Snackbar function
    function showSnackbar(message, type) {
      const snackbar = document.getElementById('snackbar');
      snackbar.textContent = message;
      snackbar.className = "snackbar show " + type;
      setTimeout(() => {
        snackbar.className = snackbar.className.replace("show " + type, "");
      }, 3000);
    }

    function validatePatientForm() {
      // You can add further validations here
      return true;
    }

    // Optional: Cluster Modal JS (if needed)
    const openClusterModalLink = document.getElementById('openClusterModal');
    const closeClusterModalBtn = document.getElementById('closeClusterModal');
    const clusterModal = document.getElementById('clusterModal');
    const clusterForm = document.getElementById('clusterForm');
    const clusterResultDiv = document.getElementById('clusterResult');
    const clusterValueSpan = document.getElementById('clusterValue');
    const useClusterBtn = document.getElementById('useClusterBtn');

    if (openClusterModalLink) {
      openClusterModalLink.addEventListener('click', (e) => {
        e.preventDefault();
        clusterModal.classList.add('active');
      });
    }
    if (closeClusterModalBtn) {
      closeClusterModalBtn.addEventListener('click', () => {
        clusterModal.classList.remove('active');
      });
    }
    if (clusterForm) {
      clusterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const randomCluster = Math.random() > 0.5 ? 1 : 0;
        clusterValueSpan.textContent = randomCluster;
        clusterResultDiv.style.display = 'block';
      });
    }
    if (useClusterBtn) {
      useClusterBtn.addEventListener('click', () => {
        document.getElementById('kidney_cluster').value = clusterValueSpan.textContent;
        clusterModal.classList.remove('active');
      });
    }
    
    // Auto-calculate BMI in the form if height and weight are entered
    const heightInput = document.getElementById("hgt_cm_tcr");
    const weightInput = document.getElementById("wgt_kg_tcr");
    const bmiInput = document.getElementById("bmi_tcr");
    function updateBMI() {
      if (heightInput && weightInput && heightInput.value && weightInput.value) {
        const heightInMeters = heightInput.value / 100;
        const bmi = (weightInput.value / (heightInMeters * heightInMeters)).toFixed(1);
        bmiInput.value = bmi;
      }
    }
    if (heightInput && weightInput) {
      heightInput.addEventListener("input", updateBMI);
      weightInput.addEventListener("input", updateBMI);
    }
  </script>
  <script type="text/javascript">
    function checkForMessage() {
      const params = new URLSearchParams(window.location.search);
      if (params.has('status') && params.has('message')) {
        const message = params.get('message');
        const status = params.get('status');
        showSnackbar(message, status);
      }
    }
  </script>
</body>
</html>
