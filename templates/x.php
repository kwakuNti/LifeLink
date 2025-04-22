<?php
// Test file - save separately and run directly to test
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// Enable error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    
    // Recipients
    $mail->setFrom('your-email@gmail.com', 'Test Sender');
    $mail->addAddress('recipient@example.com');
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'PHPMailer Test';
    $mail->Body = 'This is a test email from PHPMailer';
    
    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>