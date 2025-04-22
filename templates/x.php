<?php
// Test file - save separately and run directly to test
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if vendor autoload exists
$autoloadPath = 'vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("Vendor autoload file not found. Check path: $autoloadPath");
}
require $autoloadPath;

// Enable error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log to file as backup in case display_errors doesn't work
ini_set('log_errors', 1);
ini_set('error_log', 'php-error.log');
error_log("Starting email test script");

$mail = new PHPMailer(true);
try {
    // Debug settings
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = 'echo';
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'cliffco24@gmail.com'; // YOUR GMAIL
    $mail->Password = 'nzqo jtlf kuau xtus'; // YOUR APP PASSWORD
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    
    // Recipients - MUST MATCH USERNAME FOR GMAIL
    $mail->setFrom('cliffco24@gmail.com', 'Test Sender');
    $mail->addAddress('your-recipient@example.com'); // Change to a real address you can check
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'PHPMailer Test';
    $mail->Body = 'This is a test email from PHPMailer';
    
    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    error_log("Mailer Error: " . $mail->ErrorInfo);
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>