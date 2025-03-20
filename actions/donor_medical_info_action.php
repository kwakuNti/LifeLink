<?php
session_start();
include '../config/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../templates/login.php?status=error&message=Please log in first.");
    exit();
}

$userId = $_SESSION['user_id'];
$bloodType = $_POST['blood_type'] ?? '';
$histoInfo = trim($_POST['histo_compatibility'] ?? '');

if (!$bloodType || !$histoInfo) {
    header("Location: ../templates/donor_medical_info.php?status=error&message=Please fill in all required fields.");
    exit();
}

// Process file upload if provided
$extractedInfo = '';
if (isset($_FILES['medical_doc']) && $_FILES['medical_doc']['error'] === 0) {
    $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
    $filename = $_FILES['medical_doc']['name'];
    $fileTmp = $_FILES['medical_doc']['tmp_name'];
    $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($fileExt, $allowed)) {
        header("Location: ../templates/donor_medical_info.php?status=error&message=Invalid file type.");
        exit();
    }
    
    // Move file to uploads directory
    $destination = "../uploads/medical_docs/" . time() . "_" . basename($filename);
    if (!move_uploaded_file($fileTmp, $destination)) {
        header("Location: ../templates/donor_medical_info.php?status=error&message=File upload failed.");
        exit();
    }
    
    // Process file with OCR (pseudocode):
    // Example using a PHP OCR library (e.g., TesseractOCR):
    // require_once 'vendor/autoload.php';
    // $ocr = new TesseractOCR($destination);
    // $extractedInfo = $ocr->run();
    // For now, we simulate extraction:
    $extractedInfo = "\nExtracted Data: [Simulated OCR output]";
    
    // Optionally, merge the extracted information with the manually entered data
    $histoInfo .= $extractedInfo;
}

// Insert or update donor medical information in the donors table
$stmt = $conn->prepare("SELECT id FROM donors WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Update existing record
    $stmt->close();
    $stmt = $conn->prepare("UPDATE donors SET blood_type = ?, histo_compatibility = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $bloodType, $histoInfo, $userId);
    $stmt->execute();
} else {
    // Insert new donor record
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO donors (user_id, blood_type, histo_compatibility) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $bloodType, $histoInfo);
    $stmt->execute();
}

$stmt->close();
$conn->close();

// Redirect to the next step (e.g., map page for location selection)
header("Location: ../templates/map.php?status=success&message=Medical information submitted successfully.");
exit();
?>
