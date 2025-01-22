<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" type="text/css" href="../public/css/login.css">
    <link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a81368914c.js"></script>
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
    <link rel="manifest" href="../favicon_io/site.webmanifest">
    <link rel="stylesheet" href="../public/css/homepage.css">
    <link rel="stylesheet" href="../public/css/snackbar.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div class="container">
        <div class="img">
            <img src="../assets/images/login-removebg-preview.png">
        </div>
        <div class="login-content">
            <form onsubmit="return validateForgotPasswordForm()">
                <img src="../assets/images/avatar-svgrepo-com.svg">
                <h2 class="title">Reset Password</h2>
                
                <div id="snackbar"></div> <!-- Snackbar for validation messages -->
                
                <!-- Current Password input -->
                <div class="input-div pass">
                   <div class="i"> 
                     <i class="fas fa-lock"></i>
                   </div>
                   <div class="div">
                     <h5>Current Password</h5>
                     <input type="password" class="input" id="current-password" 
                        required>
                   </div>
                </div>

                <!-- New Password input -->
                <div class="input-div pass">
                   <div class="i"> 
                     <i class="fas fa-lock"></i>
                   </div>
                   <div class="div">
                     <h5>New Password</h5>
                     <!-- Pattern for password validation:
                          At least 8 characters, includes uppercase, lowercase, digit, special character -->
                     <input type="password" class="input" id="new-password" 
                        pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$"
                        title="Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one number, and one special character"
                        required>
                   </div>
                </div>

                <!-- Confirm New Password input -->
                <div class="input-div pass">
                   <div class="i"> 
                     <i class="fas fa-lock"></i>
                   </div>
                   <div class="div">
                     <h5>Confirm New Password</h5>
                     <input type="password" class="input" id="confirm-password" 
                        required>
                   </div>
                </div>

                <input type="submit" class="btn" value="Reset Password">
            </form>
        </div>
    </div>
    <script>
        function validateForgotPasswordForm() {
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            if (newPassword !== confirmPassword) {
                const snackbar = document.getElementById('snackbar');
                snackbar.innerHTML = "New password and confirm password do not match.";
                snackbar.className = "show";
                setTimeout(() => snackbar.className = snackbar.className.replace("show", ""), 3000);
                return false;
            }
            return true;
        }
    </script>
        <script type="text/javascript" src="../public/js/forgot.js"></script>

</body>
</html>
