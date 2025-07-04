<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" type="text/css" href="../public/css/login.css">
    <link href="https://fonts.googleapis.com/css?family=Poppins:600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a81368914c.js"></script>
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
    <link rel="manifest" href="../favicon_io/site.webmanifest">
    <link rel="stylesheet" href="../public/css/homepage.css">
    <link rel="stylesheet" href="../public/css/snackbar.css">
</head>
<body onload="checkForMessage()">
    <div class="container">
        
    <div class="img" style="position: relative;">
    <img src="../assets/images/login-removebg-preview.png" alt="Login Image">
            <!-- The link is absolutely positioned on top of the image -->
    <a href="hospital_selector" class="hospital-portal-link">
      Hospital Portal
    </a>
        </div>
        <div class="login-content">
            <form  action="../actions/signup.php" method="POST" onsubmit="return validateForm()">
                <img src="../assets/images/avatar-svgrepo-com.svg" alt="Avatar">
                
                <h2 class="title">Sign Up</h2>

                <div id="snackbar"></div>
                <a href="../actions/google-login.php" style="display: flex; align-items: center; justify-content: center; text-decoration: none;">
    <p style="text-decoration: underline; margin: 0; margin-right: 10px;">Or login with Google</p>	
    <img src="../assets/images/google-icon.png" style="height: 30px;" alt="google-logo"/>
</a>
                <!-- First Name -->
                <div class="input-div one">
                    <div class="i">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="div">
                        <h5>First Name</h5>
                        <input type="text" class="input" name="firstName"                                pattern="^[A-Za-z]+(-[A-Za-z]+)*$" 
                        title="First name should only contain letters." required>
                    </div>
                </div>

                <!-- Last Name -->
                <div class="input-div one">
                    <div class="i">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="div">
                        <h5>Last Name</h5>
                        <input type="text" class="input" name="lastName"                                pattern="^[A-Za-z]+(-[A-Za-z]+)*$" 
                        title="Last name should only contain letters." required>
                    </div>
                </div>

                <!-- Email -->
                <div class="input-div one">
                    <div class="i">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="div">
                        <h5>Email</h5>
                        <input type="email" class="input" name="email"  
       title="Enter a valid email address." required>
                    </div>
                </div>

                <!-- Password -->
                <div class="input-div pass">
                    <div class="i"> 
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="div">
                        <h5>Password</h5>
                        <input type="password" class="input" name="password" id="password" pattern=".{6,}" title="Password must be at least 6 characters long." required>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="input-div pass">
                    <div class="i"> 
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="div">
                        <h5>Confirm Password</h5>
                        <input type="password" class="input" name="confirmPassword" id="confirmPassword" required>
                    </div>
                </div>

                <a href="login">Already have an account? Login</a>

                <input type="submit" class="btn" value="Sign Up">
               
            </form>
        </div>
    </div>
    <style>
  .hospital-portal-link {
  position: absolute;
  top: 50px;      /* distance from top edge of .img container */
  right:  170px;    /* distance from right edge of .img container */
  padding: 0.5rem 1rem;
  background-color: #222;
  color: #fff;
  border-radius: 5px;
  font-weight: 900;
  text-decoration: none;
  font-size: larger;
}.hospital-portal-link:hover {
  color: #fff; /* keeps the text white on hover */
  text-decoration: none;
}




    </style>
    <script type="text/javascript">
        function checkForMessage() {
            const params = new URLSearchParams(window.location.search);
            if (params.has('status') && params.has('message')) {
                const message = params.get('message');
                const status = params.get('status');
                showSnackbar(message, status);
            }
        }

        function showSnackbar(message, type) {
            let snackbar = document.getElementById("snackbar");
            snackbar.innerHTML = message;
            snackbar.className = "show " + type;
            setTimeout(() => {
                snackbar.className = snackbar.className.replace("show", "");
            }, 3000);
        }
    </script>
    <!-- JavaScript Validation -->
    <script type="text/javascript" src="../public/js/login.js"></script>

</body>
</html>
