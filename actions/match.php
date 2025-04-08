<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/connection.php';

session_start();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['find_match'])) {
        $donor = getDonorProfile($conn, $_SESSION['user_id']);
        $_SESSION['matches'] = findCompatibleMatches($donor);
        header("Location: match.php");
        exit;
    }
    
    if (isset($_POST['predict'])) {
        $recipientId = (int)$_POST['recipient_id'];
        $donor = getDonorProfile($conn, $_SESSION['user_id']);
        $recipient = getRecipient($conn, $recipientId);
        $_SESSION['prediction'] = predictTransplantSuccess($donor, $recipient);
        header("Location: match.php");
        exit;
    }
}

// Get current donor
$donor = getDonorProfile($conn, $_SESSION['user_id']);