<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
  <link rel="manifest" href="../favicon_io/site.webmanifest">
  <title>Patient Registration</title>
  <link rel="stylesheet" href="../public/css/snackbar.css">
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
    /* Main Container */
    .container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 6rem;
      border-radius: 10px;
    }
    .page-title {
      text-align: center;
      font-size: 2rem;
      margin-bottom: 1.5rem;
      color: #1e3a8a;
      font-weight: 700;
    }
    
    /* Form Layout - Two Column */
    .form-row {
      display: flex;
      flex-wrap: wrap;
      gap: 2rem;
      margin-bottom: 1rem;
    }
    .form-col {
      flex: 1;
      min-width: 300px;
    }
    
    /* Form Fields */
    .form-group {
      margin-bottom: 1.5rem;
      position: relative;
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
    textarea {
      resize: vertical;
    }
    small.text-muted {
      display: block;
      margin-top: 0.25rem;
      font-size: 0.85rem;
      color: #777;
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
    
    /* Google Places autocomplete styling */
    .pac-container {
      font-family: 'Poppins', sans-serif;
      border-radius: 5px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
    }
    .pac-item {
      padding: 8px;
      font-size: 14px;
    }
    .pac-item:hover {
      background-color: #f5f5f5;
    }
    .pac-icon {
      margin-right: 10px;
    }
  </style>
</head>
<body onload="checkForMessage()">
  <div class="container">
    <h2 class="page-title">Patient Registration</h2>
    <form action="../actions/register_patient.php" method="POST" onsubmit="return validatePatientForm()">
      <div class="form-row">
        <!-- Left Column -->
        <div class="form-col">
          <!-- Patient Name -->
          <div class="form-group">
            <label for="patient_name">Full Name:</label>
            <input type="text" id="patient_name" name="patient_name" required placeholder="Enter your full name">
          </div>
          
          <!-- Email -->
          <div class="form-group">
            <label for="patient_email">Email:</label>
            <input type="email" id="patient_email" name="patient_email" required placeholder="Enter your email">
          </div>
          
          <!-- Password -->
          <div class="form-group">
            <label for="patient_password">Password:</label>
            <input type="password" id="patient_password" name="patient_password" required 
                   pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$" 
                   title="Password must be at least 8 characters long, include an uppercase letter, a number, and a special character">
            <small class="text-muted">Must be at least 8 characters with an uppercase letter, a number, and a special character</small>
          </div>
          
          <!-- Confirm Password -->
          <div class="form-group">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
          </div>
        </div>
        
        <!-- Right Column -->
        <div class="form-col">
          <!-- Phone Number -->
          <div class="form-group">
            <label for="patient_phone">Phone Number:</label>
            <input type="tel" id="patient_phone" name="patient_phone" placeholder="Enter your phone number">
          </div>
          
          <!-- Location with Google Places -->
          <div class="form-group">
            <label for="patient_location">Location:</label>
            <input type="text" id="patient_location" name="patient_location" placeholder="Enter your address">
            <small class="text-muted">Start typing for address suggestions</small>
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">
          </div>
          
          <!-- Description / Story -->
          <div class="form-group">
            <label for="patient_description">About You (Optional):</label>
            <textarea id="patient_description" name="patient_description" rows="4" placeholder="Tell us a little about yourself (optional)"></textarea>
          </div>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="submit-container">
        <button type="submit" class="btn">Next</button>
      </div>
    </form>
    <div id="snackbar"></div>
  </div>
  
  <!-- Load Google Maps JavaScript API -->
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDlzRfWUaPWKVws7I8Y7iTqk5_kYl3ZYm4&libraries=places&callback=initAutocomplete&loading=async" defer></script>  
  <script>
    // Initialize Google Places Autocomplete
    function initAutocomplete() {
      const input = document.getElementById('patient_location');
      const autocomplete = new google.maps.places.Autocomplete(input, {
        types: ['address'],
        fields: ['formatted_address', 'geometry']
      });
      
      // When a place is selected, populate hidden fields with the coordinates
      autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        if (!place.geometry) {
          // User entered the name of a Place that was not suggested and
          // pressed the Enter key, or the Place Details request failed.
          window.alert("No details available for input: '" + place.name + "'");
          return;
        }
        
        // Set the values of hidden fields with the latitude and longitude
        document.getElementById('latitude').value = place.geometry.location.lat();
        document.getElementById('longitude').value = place.geometry.location.lng();
        
        // Set the input field to the formatted address
        input.value = place.formatted_address;
      });
    }
    
    function checkForMessage() {
      const params = new URLSearchParams(window.location.search);
      if (params.has('status') && params.has('message')) {
        const message = params.get('message');
        const status = params.get('status');
        showSnackbar(message, status);
      }
    }
    
    function showSnackbar(message, type) {
      const snackbar = document.getElementById('snackbar');
      snackbar.textContent = message;
      snackbar.className = "snackbar show " + type;
      setTimeout(() => {
        snackbar.className = snackbar.className.replace("show " + type, "");
      }, 3000);
    }
    
    function validatePatientForm() {
      const name = document.getElementById('patient_name').value.trim();
      const email = document.getElementById('patient_email').value.trim();
      const password = document.getElementById('patient_password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      
      if (!name || !email || !password || !confirmPassword) {
        showSnackbar("Please fill in all required fields.", "error");
        return false;
      }
      if (password !== confirmPassword) {
        showSnackbar("Passwords do not match.", "error");
        return false;
      }
      
      return true;
    }
  </script>
  <script type="text/javascript" src="../public/js/login.js"></script>
</body>
</html>