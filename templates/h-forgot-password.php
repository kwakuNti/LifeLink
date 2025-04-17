<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
  <title>Forgot Password</title>
  <link rel="stylesheet" type="text/css" href="../public/css/h-login.css">
  <link rel="stylesheet" href="../public/css/homepage.css">
  <link rel="stylesheet" href="../public/css/snackbar.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    /* Using the same basic styling as your hospital login page */
    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      flex-direction: column;
    }
    .logo {
      display: none; /* Remove logo as requested */
    }
    .form-title {
      font-family: 'Poppins', sans-serif;
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 20px;
      text-align: center;
    }
    .input-div {
      width: 100%;
      max-width: 400px;
      margin: 10px auto;
      position: relative;
      border-bottom: 2px solid #d9d9d9;
    }
    .input-div.one .div,
    .input-div.pass .div {
      height: 45px;
    }
    .input-div.one .i,
    .input-div.pass .i {
      width: 30px;
      height: 30px;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .input-div .div h5 {
      font-size: 16px;
      color: #999;
      margin: 0;
    }
    .input-div .div .input {
      width: 100%;
      height: 40px;
      border: none;
      outline: none;
      font-size: 16px;
      background: none;
      color: #333;
    }
    .btn {
      margin-top: 20px;
      background-color: #333;
      color: white;
      font-size: 16px;
      border: none;
      border-radius: 5px;
      padding: 10px;
      cursor: pointer;
      width: 100%;
      max-width: 460px;
      text-align: center;
    }
    .btn:hover {
      background-color: #555;
    }
    a {
      font-size: 14px;
      color: #333;
      text-decoration: none;
      margin-top: 10px;
    }
    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body onload="checkForMessage()">
  <div class="container">
    <!-- Hospital Icon and dynamic hospital name display (if available) -->
    <div class="logo">
      <p id="hospitalDisplay" style="font-family: 'Poppins', sans-serif; font-size: 18px; color: #333; margin-bottom: 20px;"></p>
      <img src="../assets/images/icon-hospital.png" alt="Hospital Icon">
    </div>
    <h2 class="form-title" id="formTitle">Reset Your Password</h2>
    <form action="../actions/forgot-password_action.php" method="POST" onsubmit="return validateForgotPassword()">
      <!-- Username (fixed, as usernames are not changeable) -->
      <div class="input-div one">
        <div class="i">
          <i class="fas fa-user"></i>
        </div>
        <div class="div">
          <h5>Username</h5>
          <input type="text" class="input" id="username" name="username" required>
        </div>
      </div>
      <!-- New Password -->
      <div class="input-div pass">
        <div class="i">
          <i class="fas fa-lock"></i>
        </div>
        <div class="div">
          <h5>New Password</h5>
          <input type="password" class="input" id="newPassword" name="newPassword"
                 pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$"
                 title="Password must be at least 8 characters long, contain at least one uppercase letter, one number, and one special character"
                 required>
        </div>
      </div>
      <!-- Confirm New Password -->
      <div class="input-div pass">
        <div class="i">
          <i class="fas fa-lock"></i>
        </div>
        <div class="div">
          <h5>Confirm New Password</h5>
          <input type="password" class="input" id="confirmPassword" name="confirmPassword" required>
        </div>
      </div>
      <!-- Forgot Password is not needed here, as this is the reset page -->
      <input type="submit" class="btn" value="Reset Password">
    </form>
    <div id="snackbar"></div>
  </div>

  <script>
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
      snackbar.className = 'snackbar show ' + type;
      setTimeout(() => {
        snackbar.className = snackbar.className.replace('show ' + type, '');
      }, 3000);
    }
    
    function validateForgotPassword() {
      const username = document.getElementById('username').value.trim();
      const newPassword = document.getElementById('newPassword').value.trim();
      const confirmPassword = document.getElementById('confirmPassword').value.trim();
      
      if (!username || !newPassword || !confirmPassword) {
        showSnackbar("Please fill in all fields.", "error");
        return false;
      }
      if (newPassword !== confirmPassword) {
        showSnackbar("Passwords do not match.", "error");
        return false;
      }
      return true;
    }
    
    // Optional: If this page is accessed with a hospital_id parameter, you can dynamically show the hospital name.
    const hospitalData = {
      "1": "Korle Bu Teaching Hospital",
      "2": "The Bank Hospital",
      "3": "Komfo Anokye Teaching Hospital",
      "4": "37 Military Hospital",
      "5": "University of Ghana Medical Centre (UGMC)"
    };
    
    const params = new URLSearchParams(window.location.search);
    const hospitalId = params.get('hospital_id');
    if (hospitalId && hospitalData[hospitalId]) {
      document.getElementById('hospitalDisplay').textContent = hospitalData[hospitalId];
      document.getElementById('formTitle').textContent = "Reset Password for " + hospitalData[hospitalId];
    }
  </script>
  <script type="text/javascript" src="../public/js/login.js"></script>
</body>
</html>
