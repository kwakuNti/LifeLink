<?php
session_start();
// Include database connection
include '../config/connection.php';

// Determine if this is a first-time login
$first_login = isset($_GET['first_login']) && $_GET['first_login'] == '1';
$hospital_id = $_GET['hospital_id'] ?? '';
$username = $_GET['username'] ?? '';
$token = $_GET['token'] ?? '';

// Check if user is already authenticated from login screen (first-time login case)
$authenticated = false;
if ($first_login && isset($_SESSION['hospital_id']) && isset($_SESSION['password_reset_required'])) {
    $authenticated = true;
}

// Validate token for password reset from forgot password flow
$valid_token = false;
if (!$authenticated && !empty($token) && !empty($username)) {
    if ($stmt = $conn->prepare("SELECT id FROM hospitals WHERE username = ? AND password_reset_token = ? AND password_reset_expires > NOW()")) {
        $stmt->bind_param("ss", $username, $token);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $hospital = $result->fetch_assoc();
            $valid_token = true;
            $hospital_id = $hospital['id'];
        }
        $stmt->close();
    }
}

// If not authenticated and no valid token, redirect back to login
if (!$authenticated && !$valid_token) {
    header("Location: ../templates/hospital_login.php?status=error&message=" . urlencode("Invalid or expired password reset link"));
    exit();
}

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $hospital_id = $_POST['hospital_id'] ?? '';
    $username = $_POST['username'] ?? '';
    
    // Validate passwords
    if (empty($new_password) || empty($confirm_password)) {
        $message = "Both password fields are required";
        $messageType = "error";
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match";
        $messageType = "error";
    } elseif (strlen($new_password) < 8) {
        $message = "Password must be at least 8 characters long";
        $messageType = "error";
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $message = "Password must contain at least one uppercase letter";
        $messageType = "error";
    } elseif (!preg_match('/\d/', $new_password)) {
        $message = "Password must contain at least one number";
        $messageType = "error";
    } elseif (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
        $message = "Password must contain at least one special character";
        $messageType = "error";
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update the password in the database
        if ($stmt = $conn->prepare("UPDATE hospitals SET password = ?, last_password_change = NOW(), password_reset_token = NULL, password_reset_expires = NULL WHERE id = ? AND username = ?")) {
            $stmt->bind_param("sis", $hashed_password, $hospital_id, $username);
            if ($stmt->execute()) {
                // Clear any password reset required flag
                unset($_SESSION['password_reset_required']);
                
                // Set success message and redirect
                $message = "Password updated successfully! You can now log in with your new password.";
                $messageType = "success";
                
                // If they were already logged in, redirect to admin page
                if ($authenticated) {
                    header("Location: ../templates/hospital-admin.php?status=success&message=" . urlencode("Password updated successfully!"));
                    exit();
                }
            } else {
                $message = "Error updating password: " . $conn->error;
                $messageType = "error";
            }
            $stmt->close();
        } else {
            $message = "Database error: " . $conn->error;
            $messageType = "error";
        }
    }
}

// Get hospital name if hospital_id is available
$hospital_name = "Hospital";
if (!empty($hospital_id)) {
    if ($stmt = $conn->prepare("SELECT name FROM hospitals WHERE id = ?")) {
        $stmt->bind_param("i", $hospital_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $hospital = $result->fetch_assoc();
            $hospital_name = $hospital['name'];
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Hospital Password</title>
    <link rel="stylesheet" type="text/css" href="../public/css/h-login.css">
    <link rel="stylesheet" href="../public/css/homepage.css">
    <link rel="stylesheet" href="../public/css/snackbar.css">
    <style>
        /* Similar styles as the login page */
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
        .input-div {
            width: 100%;
            max-width: 400px;
            margin: 10px auto;
            position: relative;
            border-bottom: 2px solid #d9d9d9;
        }
        .input-div .div {
            height: 45px;
        }
        .input-div .i {
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
            background-color: #333;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            padding: 10px;

        }
        .btn:hover {
            background-color: #555;
        }
        a {
            font-size: 14px;
            color: #333;
            text-decoration: none;
            margin-top: 10px;
            display: block;
            text-align: center;
        }
        a:hover {
            text-decoration: underline;
        }
        .password-requirements {
            max-width: 400px;
            font-size: 14px;
            color: #666;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .password-requirements ul {
            margin: 5px 0;
            padding-left: 20px;
        }
        /* Snackbar styling in case external CSS isn't loading */
        #snackbar {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 2px;
            padding: 16px;
            position: fixed;
            z-index: 1;
            left: 50%;
            bottom: 30px;
        }
        #snackbar.show {
            visibility: visible;
            animation: fadein 0.5s, fadeout 0.5s 2.5s;
        }
        #snackbar.error { background-color: #d32f2f; }
        #snackbar.success { background-color: #43a047; }
        #snackbar.warning { background-color: #ff9800; }
        @keyframes fadein {
            from {bottom: 0; opacity: 0;}
            to {bottom: 30px; opacity: 1;}
        }
        @keyframes fadeout {
            from {bottom: 30px; opacity: 1;}
            to {bottom: 0; opacity: 0;}
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Hospital Info (icon and name) -->
        <div class="logo">
            <img src="../assets/images/icon-hospital.png" alt="Hospital Icon">
        </div>
        
        <h2 class="form-title">
            <?php echo $first_login ? "Welcome to " . htmlspecialchars($hospital_name) : "Reset Password"; ?>
        </h2>
        
        <?php if ($first_login): ?>
        <div class="password-requirements">
            <p><strong>This is your first login.</strong> Please set a new password to continue.</p>

        </div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" onsubmit="return validateForm()">
            <!-- Hidden inputs to pass data -->
            <input type="hidden" name="hospital_id" value="<?php echo htmlspecialchars($hospital_id); ?>">
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
            
            <!-- New Password -->
            <div class="input-div pass">
                <div class="i">
                    <i class="fas fa-lock"></i>
                </div>
                <div class="div">
                    <input type="password" class="input" id="new_password" name="new_password" 
                           pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$"
                           title="Password must be at least 8 characters long, contain at least one uppercase letter, one number, and one special character"
                           required>
                </div>
            </div>
            
            <!-- Confirm Password -->
            <div class="input-div pass">
                <div class="i">
                    <i class="fas fa-lock"></i>
                </div>
                <div class="div">
                    <input type="password" class="input" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            
            <!-- Password strength meter -->
            <div style="width: 100%; max-width: 400px; margin: 10px auto;">
                <div id="password-strength-meter" style="height: 5px; width: 100%; background-color: #eee; margin-top: 5px;">
                    <div id="password-strength-bar" style="height: 100%; width: 0%; transition: width 0.3s;"></div>
                </div>
                <p id="password-strength-text" style="margin: 5px 0; font-size: 12px; color: #666;">Password strength</p>
            </div>
            
            <!-- Submit Button -->
            <input type="submit" class="btn" value="Reset Password">
            
            <!-- Link back to login -->
            <a href="hospital_login.php">Back to Login</a>
        </form>
        
        <div id="snackbar"></div>
    </div>
    
    <script>
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Check for messages
            <?php if (!empty($message)): ?>
                showSnackbar("<?php echo addslashes($message); ?>", "<?php echo $messageType; ?>");
            <?php endif; ?>
            
            // Password strength checker
            const passwordInput = document.getElementById('new_password');
            const strengthBar = document.getElementById('password-strength-bar');
            const strengthText = document.getElementById('password-strength-text');
            
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strength = checkPasswordStrength(password);
                
                // Update strength bar
                strengthBar.style.width = strength.score + '%';
                strengthBar.style.backgroundColor = strength.color;
                strengthText.textContent = strength.message;
            });
        });
        
        function showSnackbar(message, type) {
            const snackbar = document.getElementById('snackbar');
            snackbar.textContent = message;
            snackbar.className = 'show ' + (type || 'info');
            setTimeout(() => {
                snackbar.className = snackbar.className.replace('show ' + (type || 'info'), '');
            }, 3000);
        }
        
        function validateForm() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                showSnackbar("Passwords do not match", "error");
                return false;
            }
            
            // Password complexity check (client-side validation)
            if (newPassword.length < 8) {
                showSnackbar("Password must be at least 8 characters long", "error");
                return false;
            }
            
            if (!/[A-Z]/.test(newPassword)) {
                showSnackbar("Password must contain at least one uppercase letter", "error");
                return false;
            }
            
            if (!/\d/.test(newPassword)) {
                showSnackbar("Password must contain at least one number", "error");
                return false;
            }
            
            if (!/[^A-Za-z0-9]/.test(newPassword)) {
                showSnackbar("Password must contain at least one special character", "error");
                return false;
            }
            
            return true;
        }
        
        function checkPasswordStrength(password) {
            let score = 0;
            let message = "Very weak";
            let color = "#ff4d4d"; // Red
            
            // Length check
            if (password.length >= 8) score += 20;
            if (password.length >= 12) score += 10;
            
            // Character type checks
            if (/[A-Z]/.test(password)) score += 20;
            if (/[a-z]/.test(password)) score += 10;
            if (/\d/.test(password)) score += 20;
            if (/[^A-Za-z0-9]/.test(password)) score += 20;
            
            // Determine strength message and color
            if (score >= 90) {
                message = "Very strong";
                color = "#2e7d32"; // Dark green
            } else if (score >= 70) {
                message = "Strong";
                color = "#4caf50"; // Green
            } else if (score >= 50) {
                message = "Moderate";
                color = "#ff9800"; // Orange
            } else if (score >= 30) {
                message = "Weak";
                color = "#ff5722"; // Orange-red
            }
            
            return {
                score: score,
                message: message,
                color: color
            };
        }
    </script>
</body>
</html>