<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="apple-touch-icon" href="../favicon_io/apple-touch-icon.png" sizes="180x180">
  <link rel="icon" type="image/png" href="../favicon_io/favicon-32x32.png" sizes="32x32">
  <link rel="icon" type="image/png" href="../favicon_io/favicon-16x16.png" sizes="16x16">
  <link rel="manifest" href="../favicon_io/site.webmanifest">
  <title>Hospital Login</title>

  <!-- external css -->
  <link rel="stylesheet" href="../public/css/h-login.css">
  <link rel="stylesheet" href="../public/css/homepage.css">
  <link rel="stylesheet" href="../public/css/snackbar.css">

  <style>
  /* =============== page-specific minimal styling =============== */
  .container{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh}
  .logo img{width:260px}
  .form-title{font-family:'Poppins',sans-serif;font-size:24px;font-weight:700;margin:20px 0;text-align:center}
  .input-div{max-width:400px;width:100%;margin:12px 0;position:relative;border-bottom:2px solid #d9d9d9}
  .input-div .input{width:100%;height:40px;border:none;background:none;font-size:16px}
  .btn{margin-top:20px;max-width:460px;width:100%;padding:10px;border:none;border-radius:5px;background:#333;color:#fff;font-size:16px;cursor:pointer}
  .btn:hover{background:#555}
  a{display:block;margin-top:8px;font-size:14px;color:#333;text-decoration:none}
  a:hover{text-decoration:underline}

  /* fallback snackbar   (snackbar.css will override) */
  #snackbar{position:fixed;left:30px;top:30px;z-index:9999;min-width:250px;padding:16px;border-radius:6px;background:#111;color:#fff;font-size:16px;visibility:hidden;opacity:0;transition:opacity .3s}
  #snackbar.show{visibility:visible;opacity:1}
  #snackbar.success{background:#10b981}
  #snackbar.error{background:#ef4444}
  #snackbar.warning{background:#f59e0b}
  </style>
</head>

<body onload="initializePage()">
  <div class="container">
    <div class="logo">
      <p id="hospitalDisplay" style="margin-bottom:20px;font-family:'Poppins',sans-serif;font-size:18px;color:#333"></p>
      <img src="../assets/images/icon-hospital.png" alt="Hospital Icon">
    </div>

    <h2 class="form-title" id="formTitle">Hospital Login</h2>

    <form action="../actions/hospital_login.php" method="POST" onsubmit="return validateHospitalLogin()">
      <input type="hidden" id="hospital_id" name="hospital_id">

      <div class="input-div one">
        <div class="div">
          <h5>Username</h5>
          <input type="text" class="input" id="username" name="username" required>
        </div>
      </div>

      <div class="input-div pass">
        <div class="div">
          <h5>Password</h5>
          <input type="password" class="input" id="password" name="password"
                 pattern="^(?=.*[A-Z])(?=.*\\d)(?=.*[^A-Za-z0-9]).{8,}$"
                 title="Password must be at least 8 characters long, contain at least one uppercase letter, one number, and one special character"
                 required>
        </div>
      </div>

      <a href="h-forgot-password.php">Forgot Password?</a>
      <a href="login">Back User Login</a>

      <input type="submit" class="btn" value="Login">
    </form>

    <div id="snackbar"></div>
  </div>

  <script>
  /* ---------- “database” of hospitals ---------- */
  const hospitalData={
    "1":{name:"Korle Bu Teaching Hospital",themeColor:"#fce4ec"},
    "2":{name:"The Bank Hospital",themeColor:"#e8f5e9"},
    "3":{name:"Komfo Anokye Teaching Hospital",themeColor:"#e3f2fd"},
    "4":{name:"37 Military Hospital",themeColor:"#fff3e0"},
    "5":{name:"UG Medical Centre (UGMC)",themeColor:"#f9fbe7"}
  };

  /* ---------- snackbar util (robust) ---------- */
  function showSnackbar(msg,type='info'){
    const bar=document.getElementById('snackbar');
    bar.textContent=msg;
    bar.classList.remove('success','error','warning','info','show');
    bar.classList.add(type,'show');
    clearTimeout(bar._timer);
    bar._timer=setTimeout(()=>bar.classList.remove('show'),3000);
  }

  /* ---------- page init ---------- */
  function initializePage(){
    const url=new URLSearchParams(window.location.search);
    let hid=url.get('hospital_id')||localStorage.getItem('hospital_id');
    if(hid) localStorage.setItem('hospital_id',hid);
    document.getElementById('hospital_id').value=hid||'';

    if(hid && hospitalData[hid]){
      const h=hospitalData[hid];
      document.body.style.backgroundColor=h.themeColor;
      document.getElementById('formTitle').textContent='Welcome to '+h.name;
      document.getElementById('hospitalDisplay').textContent=h.name;
    }else{
      showSnackbar('Invalid hospital selection','error');
      document.getElementById('hospitalDisplay').textContent='Unknown Hospital';
    }
  }

  /* ---------- form validation ---------- */
  function validateHospitalLogin(){
    const u=document.getElementById('username').value.trim(),
          p=document.getElementById('password').value.trim();
    if(!u || !p){
      showSnackbar('Please enter username and password','error');
      return false;
    }
    return true;
  }
  </script>

  <!-- keep any additional js you need -->
  <script src="../public/js/login.js"></script>
</body>
</html>
