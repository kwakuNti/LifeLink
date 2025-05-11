<?php
// Start session if needed for other purposes
session_start();
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
<body onload="initializePage()">
  <div class="container">
    <!-- Hospital Info (icon and name) -->
    <div class="logo">
      <p id="hospitalDisplay" style="font-family: 'Poppins', sans-serif; font-size: 18px; color: #333; margin-bottom: 20px;"></p>
      <img src="../assets/images/icon-hospital.png" alt="Hospital Icon">
    </div>
    <h2 class="form-title" id="formTitle">Hospital Login</h2>
    <form action="../actions/hospital_login.php" method="POST" onsubmit="return validateHospitalLogin()">
      <!-- Hidden input to persist hospital_id -->
      <input type="hidden" id="hospital_id" name="hospital_id" value="">
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
      <a href="login">Back User Login</a>

      <!-- Submit Button -->
      <input type="submit" class="btn" value="Login">
    </form>
    <div id="snackbar"></div>
  </div>
  
  <script>
    // Hardcoded mapping for hospital details (could be replaced with dynamic lookup)
    const hospitalData = {
      "1": { name: "Korle Bu Teaching Hospital", themeColor: "#fce4ec" },
      "2": { name: "The Bank Hospital", themeColor: "#e8f5e9" },
      "3": { name: "Komfo Anokye Teaching Hospital", themeColor: "#e3f2fd" },
      "4": { name: "37 Military Hospital", themeColor: "#fff3e0" },
      "5": { name: "University of Ghana Medical Centre (UGMC)", themeColor: "#f9fbe7" }
    };

    // Use localStorage to persist hospital_id across page reloads or error redirects
    function initializePage() {
      // Try to get hospital_id from URL
      const urlParams = new URLSearchParams(window.location.search);
      let hospitalId = urlParams.get('hospital_id');
      
      // If not in URL, try localStorage
      if (!hospitalId) {
        hospitalId = localStorage.getItem('hospital_id');
      } else {
        // Save hospital_id to localStorage
        localStorage.setItem('hospital_id', hospitalId);
      }
      
      // Save hospital_id in the hidden input so it is submitted with the form
      document.getElementById('hospital_id').value = hospitalId || "";
      
      // If hospitalId is valid, update page details
      if (hospitalId && hospitalData[hospitalId]) {
        const hospital = hospitalData[hospitalId];
        // document.getElementById('hospitalDisplay').textContent = hospital.name;
        document.body.style.backgroundColor = hospital.themeColor;
        document.getElementById('formTitle').textContent = "Welcome to " + hospital.name;
      } else {
        showSnackbar("Invalid hospital selection.", "error");
        document.getElementById('hospitalDisplay').textContent = "Unknown Hospital";
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
  </script>
  <style>
/* Fallback if snackbar.css is missing */
#snackbar{
  position:fixed;left:30px;top:30px;z-index:9999;
  min-width:250px;padding:16px;border-radius:6px;
  background:#111;color:#fff;font-size:16px;
  visibility:hidden;opacity:0;transition:opacity .3s;
}
#snackbar.show{visibility:visible;opacity:1}
#snackbar.success{background:#10b981}
#snackbar.error  {background:#ef4444}
#snackbar.warning{background:#f59e0b}
</style>
</head>
<body onload="initializePage()">
…
<div id="snackbar"></div>

<script>
/* ---------- Snackbar utility ---------- */
function showSnackbar(msg,type='info'){
  const bar=document.getElementById('snackbar');
  bar.textContent=msg;

  // purge previous state
  bar.classList.remove('success','error','warning','info','show');
  // add new state
  bar.classList.add(type,'show');

  // auto-hide after 3 s
  clearTimeout(bar._timer);
  bar._timer=setTimeout(()=>bar.classList.remove('show'),3000);
}

/* ---------- Page init ---------- */
function initializePage(){
  const urlParams=new URLSearchParams(window.location.search);
  let hid=urlParams.get('hospital_id')||localStorage.getItem('hospital_id');

  if(hid) localStorage.setItem('hospital_id',hid);
  document.getElementById('hospital_id').value=hid||'';

  if(hid && hospitalData[hid]){
     const h=hospitalData[hid];
     document.body.style.backgroundColor=h.themeColor;
     document.getElementById('formTitle').textContent='Welcome to '+h.name;
  }else{
     showSnackbar('Invalid hospital selection','error');
  }
}

/* ---------- Form validation ---------- */
function validateHospitalLogin(){
  const u=document.getElementById('username').value.trim();
  const p=document.getElementById('password').value.trim();
  if(!u||!p){
     showSnackbar('Please enter username and password','error');
     return false;
  }
  return true;
}
</script>
  <script type="text/javascript" src="../public/js/login.js"></script>
</body>
</html>
