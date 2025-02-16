<?php
session_start();
include '../config/connection.php';

// Load PHPMailer for sending emails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
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

        // Send OTP email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'cliffco24@gmail.com';
            $mail->Password = 'nzqo jtlf kuau xtus'; // Use App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email Headers
            $mail->setFrom('cliffco24@gmail.com', 'LifeLink Support');
            $mail->addAddress($email);
            $mail->addReplyTo('support@lifelink.com', 'LifeLink Support');
            $mail->Subject = "Verify Your Account - LifeLink";
            $mail->isHTML(true);

            // Email Content
            $mail->Body = "
                <h2>Welcome to LifeLink</h2>
                <p>Your OTP code is: <strong>$otp</strong></p>
                <p>Please enter this code to verify your email.</p>
                <br>
                <p>Need help? Contact <a href='mailto:support@lifelink.com'>support@lifelink.com</a>.</p>
            ";
            $mail->AltBody = "Your OTP code is: $otp. Please enter this code to verify your email.";

            if ($mail->send()) {
                // Redirect to OTP verification page
                header("Location: ../templates/verify-otp.php?status=success&message=OTP sent to your email.");
                exit();
            } else {
                header("Location: ../templates/sign-up.php?status=error&message=Failed to send OTP email.");
                exit();
            }
        } catch (Exception $e) {
            header("Location: ../templates/sign-up.php?status=error&message=Mailer Error: " . $mail->ErrorInfo);
            exit();
        }
    } else {
        header("Location: ../templates/sign-up.php?status=error&message=Registration failed. Try again.");
        exit();
    }
}
?>
