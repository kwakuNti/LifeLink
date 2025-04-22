<?php
session_start();
include '../config/connection.php';

// Load PHPMailer for sending emails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

// Enable error reporting for troubleshooting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 0 for production, 1 for testing
ini_set('log_errors', 1);
ini_set('error_log', '../logs/email-errors.log');

// Direct configuration instead of using include file
// IMPORTANT: Change these after testing is complete
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'cliffco24@gmail.com');
define('SMTP_PASSWORD', 'nzqo jtlf kuau xtus');
define('EMAIL_FROM', 'cliffco24@gmail.com');
define('EMAIL_FROM_NAME', 'LifeLink Support');
define('EMAIL_REPLY_TO', 'cliffco24@gmail.com');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST["firstName"]);
    $lastName = trim($_POST["lastName"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];

    // Validate input fields
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        header("Location: ../templates/sign-up?status=error&message=All fields are required!");
        exit();
    }

    if ($password !== $confirmPassword) {
        header("Location: ../templates/sign-up?status=error&message=Passwords do not match!");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../templates/sign-up?status=error&message=Invalid email format!");
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        header("Location: ../templates/sign-up?status=error&message=Email is already registered!");
        exit();
    }
    $stmt->close();

    // Generate OTP (6-digit)
    $otp = strval(rand(100000, 999999));
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into DB with OTP (Unverified)
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, otp, is_verified) VALUES (?, ?, ?, 'donor', ?, FALSE)");
    $fullName = $firstName . " " . $lastName;
    $stmt->bind_param("ssss", $fullName, $email, $hashedPassword, $otp);
    
    if ($stmt->execute()) {
        // Save email and a flag in session so that the OTP page can verify the flow
        $_SESSION['otp_email'] = $email;
        $_SESSION['otp_sent'] = true;
        $stmt->close();

        // Send OTP email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Debug mode (set to 0 in production)
            $mail->SMTPDebug = 0;  // Change to 2 for detailed debugging
            // Use 'error_log' for production, 'echo' for testing
            $mail->Debugoutput = 'error_log';
            
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            
            // Set higher timeout for SMTP connection
            $mail->Timeout = 60; // 60 seconds timeout
            
            // Add proper headers to avoid spam filters
            $mail->XMailer = 'LifeLink Mailer';
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->addCustomHeader('List-Unsubscribe', '<mailto:' . SMTP_USERNAME . '>');
            
            // Email Headers - IMPORTANT: setFrom MUST match SMTP_USERNAME for Gmail
            $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
            $mail->addAddress($email, $fullName);
            $mail->addReplyTo(EMAIL_REPLY_TO, EMAIL_FROM_NAME);
            $mail->Subject = "Your LifeLink Verification Code";
            $mail->isHTML(true);

            // Email Content - Simplified for better deliverability
            $mail->Body = "
                <!DOCTYPE html>
                <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Your Verification Code</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #4A89DC; color: white; padding: 10px 20px; text-align: center; }
                        .content { padding: 20px; background-color: #ffffff; border: 1px solid #ddd; }
                        .otp-code { font-size: 24px; font-weight: bold; color: #4A89DC; text-align: center; 
                                    padding: 10px; margin: 20px 0; background-color: #f5f5f5; border-radius: 4px; }
                        .footer { font-size: 12px; text-align: center; margin-top: 20px; color: #777; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Welcome to LifeLink</h2>
                        </div>
                        <div class='content'>
                            <p>Hello $firstName,</p>
                            <p>Thanks for signing up with LifeLink. Please use the verification code below to complete your registration:</p>
                            
                            <div class='otp-code'>$otp</div>
                            
                            <p>This code will expire in 15 minutes.</p>
                            <p>If you didn't create an account with us, you can safely ignore this email.</p>
                        </div>
                        <div class='footer'>
                            <p>© " . date('Y') . " LifeLink</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            // Plain text version for email clients that don't support HTML
            $mail->AltBody = "Hello $firstName,\n\nThanks for signing up with LifeLink. Your verification code is: $otp\n\nThis code will expire in 15 minutes.\n\nIf you didn't create an account with us, you can safely ignore this email.\n\n© " . date('Y') . " LifeLink";
            
            // Log before sending for debugging
            error_log("Attempting to send verification email to: $email");
            
            if ($mail->send()) {
                error_log("Email successfully sent to: $email");
                // Redirect to OTP verification page
                header("Location: ../templates/verify-otp?status=success&message=Verification code sent to your email");
                exit();
            } else {
                error_log("Email send failed: " . $mail->ErrorInfo);
                header("Location: ../templates/sign-up?status=error&message=Failed to send verification email");
                exit();
            }
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
            $errorMessage = urlencode("Verification email could not be sent. Please try again later.");
            header("Location: ../templates/sign-up?status=error&message=$errorMessage");
            exit();
        }
    } else {
        header("Location: ../templates/sign-up?status=error&message=Registration failed. Please try again.");
        exit();
    }
}
?>