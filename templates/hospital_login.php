<!DOCTYPE html>
<html lang="en">
<head>
  <title>Hospital Login</title>
  <link rel="stylesheet" type="text/css" href="../public/css/h-login.css">
  <link rel="stylesheet" href="../public/css/homepage.css">
  <link rel="stylesheet" href="../public/css/snackbar.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    /* Basic styling as in the organ selector page */
    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      flex-direction: column;
    }
    .logo img {
      width: 300px;
    }
    .form-title {
      font-family: 'Poppins', sans-serif;
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 20px;
      text-align: center;
    }
    /* Input field styling similar to organ selector */
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
    <!-- No logo as requested -->
    <h2 class="form-title" id="formTitle">Hospital Login</h2>
    <!-- Display hospital name dynamically -->
    <div class="logo">
    <p id="hospitalDisplay" style="font-family: 'Poppins', sans-serif; font-size: 18px; color: #333; margin-bottom: 20px;"></p>

      <img src="../assets/images/icon-hospital.png" alt="LifeLink Logo">
    </div>
    <form action="../actions/hospital_login.php" method="POST" onsubmit="return validateHospitalLogin()">
      <!-- Username -->
      <div class="input-div one">
        <div class="i">
          <i class="fas fa-user"></i>
        </div>
        <div class="div">
          <h5>Username</h5>
          <input type="text" class="input" id="username" name="username" required>
        </div>
      </div>
      <!-- Password -->
      <div class="input-div pass">
        <div class="i">
          <i class="fas fa-lock"></i>
        </div>
        <div class="div">
          <h5>Password</h5>
          <input type="password" class="input" id="password" name="password"
                 pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$"
                 title="Password must be at least 8 characters long, contain at least one uppercase letter, one number, and one special character" 
                 required>
        </div>
      </div>
      <!-- Forgot Password link -->
      <a href="h-forgot-password.php">Forgot Password?</a>
      <!-- Submit Button -->
      <input type="submit" class="btn" value="Login">
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
    
    function validateHospitalLogin() {
      const username = document.getElementById('username').value.trim();
      const password = document.getElementById('password').value.trim();
      if (!username || !password) {
        showSnackbar("Please enter username and password.", "error");
        return false;
      }
      return true;
    }
    
    // Hardcoded mapping for hospital details
    const hospitalData = {
      "1": { name: "Korle Bu Teaching Hospital", themeColor: "#fce4ec" },
      "2": { name: "The Bank Hospital", themeColor: "#e8f5e9" },
      "3": { name: "Komfo Anokye Teaching Hospital", themeColor: "#e3f2fd" },
      "4": { name: "37 Military Hospital", themeColor: "#fff3e0" },
      "5": { name: "University of Ghana Medical Centre (UGMC)", themeColor: "#f9fbe7" }
    };
    
    // Get hospital_id from URL parameters
    const params = new URLSearchParams(window.location.search);
    const hospitalId = params.get('hospital_id');
    
    if (!hospitalId || !hospitalData[hospitalId]) {
      showSnackbar("Invalid hospital selection.", "error");
      document.getElementById('hospitalDisplay').textContent = "Unknown Hospital";
    } else {
      const hospital = hospitalData[hospitalId];
      document.body.style.backgroundColor = hospital.themeColor;
      document.getElementById('formTitle').textContent = "Welcome to " + hospital.name;
    }
  </script>
  <script type="text/javascript" src="../public/js/login.js"></script>
</body>
</html>
