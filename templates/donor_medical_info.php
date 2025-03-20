<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Donor Medical Information</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../public/css/snackbar.css">
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
      background-color:rgba(240, 240, 240, 0.78);
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

    /* Main container fills screen */
    .container {
      min-height: calc(100vh - 80px); /* subtract header height */
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 2rem;
    }

    /* Form Styling */
    form {
      width: 100%;
      max-width: 700px;
      padding: 2rem;
      border-radius: 10px;
    }
    .form-title {
      text-align: center;
      font-size: 2rem;
      margin-bottom: 1.5rem;
      color: #1e3a8a;
      font-weight: 700;
    }
    .form-group {
      margin-bottom: 1.5rem;
    }
    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #333;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 0.75rem;
      font-size: 1rem;
      border: 1px solid #ddd;
      border-radius: 5px;
    }
    .form-group textarea {
      resize: vertical;
    }
    .note {
      font-size: 0.9rem;
      color: #777;
      margin-top: 0.5rem;
    }
    .btn {
      width: 100%;
      padding: 1rem;
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

    /* Responsive adjustments */
    @media (max-width: 600px) {
      form {
        padding: 1.5rem;
      }
      .form-title {
        font-size: 1.75rem;
      }
    }
  </style>
</head>
<body onload="checkForMessage()">
  <!-- Header with Logo -->
  <header>
    <div class="logo">
      <img src="../assets/images/logo-removebg-preview.png" alt="LifeLink Logo">
    </div>
  </header>

  <!-- Main Content Container -->
  <div class="container">
    <form action="../actions/donor_medical_info_action.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
      <h2 class="form-title">Donor Medical Information</h2>
      
      <!-- Blood Type -->
      <div class="form-group">
        <label for="blood_type">Blood Type:</label>
        <select name="blood_type" id="blood_type" required>
          <option value="">Select Blood Type</option>
          <option value="A+">A+</option>
          <option value="A-">A-</option>
          <option value="B+">B+</option>
          <option value="B-">B-</option>
          <option value="O+">O+</option>
          <option value="O-">O-</option>
          <option value="AB+">AB+</option>
          <option value="AB-">AB-</option>
        </select>
      </div>
      
      <!-- Histocompatibility Information -->
      <div class="form-group">
        <label for="histo_compatibility">Histocompatibility Information:</label>
        <textarea name="histo_compatibility" id="histo_compatibility" rows="4" placeholder="Enter your histocompatibility details..." required></textarea>
      </div>
      
      <!-- File Upload Option -->
      <div class="form-group">
        <label for="medical_doc">Upload Medical Document (Optional):</label>
        <input type="file" name="medical_doc" id="medical_doc" accept=".pdf,.jpg,.jpeg,.png" />
        <p class="note">Upload your medical report to automatically extract necessary information.</p>
      </div>
      
      <input type="submit" class="btn" value="Submit Medical Information" />
    </form>
  </div>

  <!-- Snackbar for Feedback Messages -->
  <div id="snackbar"></div>

  <script>
    // Check URL parameters for messages (from redirection)
    function checkForMessage() {
      const params = new URLSearchParams(window.location.search);
      if (params.has('status') && params.has('message')) {
        const message = params.get('message');
        const status = params.get('status');
        showSnackbar(message, status);
      }
    }
    
    // Custom snackbar function (Assuming similar CSS exists in snackbar.css)
    function showSnackbar(message, type) {
      const snackbar = document.getElementById("snackbar");
      snackbar.textContent = message;
      snackbar.className = "snackbar show " + type;
      setTimeout(() => {
        snackbar.className = snackbar.className.replace("show", "");
      }, 3000);
    }
    
    // Validate form fields before submission
    function validateForm() {
      const bloodType = document.getElementById("blood_type").value;
      const histo = document.getElementById("histo_compatibility").value.trim();
      
      if (!bloodType) {
        showSnackbar("Please select your blood type.", "error");
        return false;
      }
      if (!histo) {
        showSnackbar("Please provide histocompatibility information.", "error");
        return false;
      }
      return true;
    }
  </script>
  <script src="../public/js/login.js"></script>
</body>
</html>
