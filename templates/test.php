<?php
session_start();
include '../config/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../templates/login.php?status=error&message=Please log in first.");
    exit();
}

$userId = $_SESSION['user_id'];

// 1. Gather posted form data (required + optional)
$initAge        = isset($_POST['init_age']) ? (int)$_POST['init_age'] : 0;
$bmiTcr         = isset($_POST['bmi_tcr']) ? (float)$_POST['bmi_tcr'] : 0.0;
$dayswaitAlloc  = isset($_POST['dayswait_alloc']) ? (int)$_POST['dayswait_alloc'] : 0;
$kidneyCluster  = isset($_POST['kidney_cluster']) ? (int)$_POST['kidney_cluster'] : 0;

// Optional fields
$dgnTcr   = isset($_POST['dgn_tcr']) ? (float)$_POST['dgn_tcr'] : 0.0;
$wgtKgTcr = isset($_POST['wgt_kg_tcr']) ? (float)$_POST['wgt_kg_tcr'] : 0.0;
$hgtCmTcr = isset($_POST['hgt_cm_tcr']) ? (float)$_POST['hgt_cm_tcr'] : 0.0;
$gfr      = isset($_POST['gfr']) ? (float)$_POST['gfr'] : 0.0;

// On Dialysis (Y/N => boolean)
$onDialysis = $_POST['on_dialysis'] ?? 'N';
$onDialysisBool = ($onDialysis === 'Y') ? 1 : 0;

// Blood Type (A, B, AB, O)
$bloodType = $_POST['blood_type'] ?? '';
if (empty($bloodType)) {
    header("Location: ../templates/donor_medical_info.php?status=error&message=Blood type is required.");
    exit();
}

// Basic validation for required fields
if ($initAge <= 0 || $bmiTcr <= 0.0 || $dayswaitAlloc < 0) {
    header("Location: ../templates/donor_medical_info.php?status=error&message=Missing or invalid required fields.");
    exit();
}

// 2. Process file upload if provided (Optional)
$extractedInfo = '';
if (isset($_FILES['medical_doc']) && $_FILES['medical_doc']['error'] === 0) {
    $allowed = ['pdf','jpg','jpeg','png'];
    $filename = $_FILES['medical_doc']['name'];
    $fileTmp = $_FILES['medical_doc']['tmp_name'];
    $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($fileExt, $allowed)) {
        header("Location: ../templates/donor_medical_info.php?status=error&message=Invalid file type.");
        exit();
    }

    // Move file to an uploads directory
    $destination = "../uploads/medical_docs/" . time() . "_" . basename($filename);
    if (!move_uploaded_file($fileTmp, $destination)) {
        header("Location: ../templates/donor_medical_info.php?status=error&message=File upload failed.");
        exit();
    }

    // Simulate OCR extraction for demonstration
    $extractedInfo = "[Simulated OCR output from $filename]";
}

// 3. Insert or update donor medical information in the donors table
$stmt = $conn->prepare("SELECT id FROM donors WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Update existing donor record
    $stmt->close();
    $sql = "UPDATE donors
            SET 
                blood_type      = ?,
                init_age        = ?,
                bmi_tcr         = ?,
                dayswait_alloc  = ?,
                kidney_cluster  = ?,
                dgn_tcr         = ?,
                wgt_kg_tcr      = ?,
                hgt_cm_tcr      = ?,
                gfr             = ?,
                on_dialysis     = ?
            WHERE user_id = ?";
    $stmtUpdate = $conn->prepare($sql);
    $stmtUpdate->bind_param(
        "sidiidddiii",
        $bloodType,
        $initAge,
        $bmiTcr,
        $dayswaitAlloc,
        $kidneyCluster,
        $dgnTcr,
        $wgtKgTcr,
        $hgtCmTcr,
        $gfr,
        $onDialysisBool,
        $userId
    );
    $stmtUpdate->execute();
    $stmtUpdate->close();
} else {
    // Insert new donor record
    $stmt->close();
    $sql = "INSERT INTO donors (
                user_id,
                blood_type,
                init_age,
                bmi_tcr,
                dayswait_alloc,
                kidney_cluster,
                dgn_tcr,
                wgt_kg_tcr,
                hgt_cm_tcr,
                gfr,
                on_dialysis
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($sql);
    $stmtInsert->bind_param(
        "isidiidddii",
        $userId,
        $bloodType,
        $initAge,
        $bmiTcr,
        $dayswaitAlloc,
        $kidneyCluster,
        $dgnTcr,
        $wgtKgTcr,
        $hgtCmTcr,
        $gfr,
        $onDialysisBool
    );
    $stmtInsert->execute();
    $stmtInsert->close();
}

$conn->close();

// Optionally log $extractedInfo if needed

// 4. Redirect to the next step
header("Location: ../templates/map.php?status=success&message=Medical information submitted successfully.");
exit();
