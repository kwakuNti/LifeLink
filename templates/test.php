<?php
session_start();
include '../config/connection.php';

// Load PHPMailer for sending emails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;
require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST["firstName"]);
    $lastName = trim($_POST["lastName"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmPassword"];

    // Validate input fields
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        header("Location: ../templates/sign-up.php?status=error&message=All fields are required!");
        exit();
    }

    if ($password !== $confirmPassword) {
        header("Location: ../templates/sign-up.php?status=error&message=Passwords do not match!");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../templates/sign-up.php?status=error&message=Invalid email format!");
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        header("Location: ../templates/sign-up.php?status=error&message=Email is already registered!");
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
        $_SESSION['otp_email'] = $email; // Store email in session for OTP verification
        $stmt->close();

        // Send OTP email using PHPMailer with OAuth 2.0
        $mail = new PHPMailer(true);
        try {
            // OAuth 2.0 Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->AuthType = 'XOAUTH2';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            
            // Google OAuth Configuration
            $clientId = '583068263303-tk8lbmrmu22nhhahvhp4icttc0icece0.apps.googleusercontent.com';
            $clientSecret = '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';
            
            // The refresh token you obtained during the OAuth flow
            $refreshToken = '1//04YkeuB3ZCDpLCgYIARAAGAQSNwF-L9Irs4XoqK5jo6y2GD1Muh-mfshA3mAjTB3BR4ThiQ_hMH22uSdl2qe-0Pn3Oh6bPcbejwQ'; // You need to obtain this
            
            $provider = new Google([
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
            ]);
            
            $mail->setOAuth(
                new OAuth([
                    'provider' => $provider,
                    'clientId' => $clientId,
                    'clientSecret' => $clientSecret,
                    'refreshToken' => $refreshToken,
                    'userName' => 'lifelink50@gmail.com',
                ])
            );

            // Email Headers
            $mail->setFrom('lifelink50@gmail.com', 'LifeLink Support');
            $mail->addAddress($email);
            $mail->addReplyTo('lifelink50@gmail.com', 'LifeLink Support');
            $mail->Subject = "Verify Your Account - LifeLink";
            $mail->isHTML(true);
            
            // DKIM configuration (if available)
            // $mail->DKIM_domain = 'lifelink.com';
            // $mail->DKIM_private = '/path/to/private_key.pem';
            // $mail->DKIM_selector = 'selector';
            // $mail->DKIM_identity = $mail->From;

            // Improved Email Content for Better Deliverability
            $mail->Body = "
                <!DOCTYPE html>
                <html lang='en'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Verify Your Account</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #4A89DC; color: white; padding: 10px 20px; text-align: center; }
                        .content { padding: 20px; background-color: #f9f9f9; border: 1px solid #ddd; }
                        .otp-code { font-size: 24px; font-weight: bold; color: #4A89DC; text-align: center; 
                                    padding: 10px; margin: 20px 0; background-color: #eef2f8; border-radius: 4px; }
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
                            <p>Thank you for creating an account with LifeLink. To complete your registration, please verify your email address by entering the OTP code below:</p>
                            
                            <div class='otp-code'>$otp</div>
                            
                            <p>This code will expire in 15 minutes for security reasons.</p>
                            <p>If you did not create this account, please disregard this email.</p>
                        </div>
                        <div class='footer'>
                            <p>Need help? Contact <a href='mailto:support@lifelink.com'>support@lifelink.com</a></p>
                            <p>&copy; " . date('Y') . " LifeLink. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            // Plain text version for email clients that don't support HTML
            $mail->AltBody = "Hello $firstName,\n\nThank you for creating an account with LifeLink. Your OTP code is: $otp\n\nPlease enter this code to verify your email. This code will expire in 15 minutes.\n\nIf you did not create this account, please disregard this email.\n\nNeed help? Contact support@lifelink.com";

            if ($mail->send()) {
                // Redirect to OTP verification page
                header("Location: ../templates/verify-otp.php?status=success&message=OTP sent to your email.");
                exit();
            } else {
                header("Location: ../templates/sign-up.php?status=error&message=Failed to send OTP email.");
                exit();
            }
        } catch (Exception $e) {
            $errorMessage = urlencode("Mailer Error: " . str_replace(["\r", "\n"], '', $mail->ErrorInfo));
            header("Location: ../templates/sign-up.php?status=error&message=$errorMessage");
            exit();
        }
    } else {
        header("Location: ../templates/sign-up.php?status=error&message=Registration failed. Try again.");
        exit();
    }
}
?>