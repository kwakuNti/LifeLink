<!DOCTYPE html>
<html>
<head>
	<title>Login</title>
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
<body onload="checkForMessage()">
	<div class="container">
		<div class="img">
			<img src="../assets/images/login-removebg-preview.png">
		</div>
		<div class="login-content">
			<form action= "../actions/login.php" method="POST" onsubmit="return validateForm()">
				<img src="../assets/images/avatar-svgrepo-com.svg">
				<h2 class="title">Welcome</h2>
				
				<div id="snackbar"></div> <!-- Snackbar for validation messages -->

				<!-- Email input with HTML5 email validation -->
           		<div class="input-div one">
           		   <div class="i">
           		   		<i class="fas fa-user"></i>
           		   </div>
           		   <div class="div">
           		   		<h5>Email</h5>
           		   		<input type="email" class="input" id="email" required name="email">
           		   </div>
           		</div>

				<!-- Password input with regex validation -->
           		<div class="input-div pass">
           		   <div class="i"> 
           		    	<i class="fas fa-lock"></i>
           		   </div>
           		   <div class="div">
           		    	<h5>Password</h5>
           		    	<!-- Pattern for password validation:
						   At least 8 characters, includes uppercase, lowercase, digit, special character, and a dot -->
           		    	<input type="password" class="input" id="password" 
						   pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$"
						   title="Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one number, one special character" 
						   required name="password">
            	   </div>
            	</div>

				    <!-- Remember Me Checkbox -->
					<div class="checkbox">
        <input type="checkbox" id="rememberMe" name="rememberMe">
        <label for="rememberMe">Remember Me</label>
    </div>

				<a href="forgot-password.php">Forgot Password?</a>
                <a href="sign-up">Sign up</a>
            	<input type="submit" class="btn" value="Login">

            </form>
        </div>
    </div>
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
    <script type="text/javascript" src="../public/js/login.js"></script>
</body>
</html>
