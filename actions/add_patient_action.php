<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config/connection.php';

// Ensure the hospital is logged in
if (!isset($_SESSION['hospital_id'])) {
    header("Location: ../templates/hospital_login.php?status=error&message=Please log in first.");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data, including the existing patient ID
    $patient_id     = trim($_POST['patient_id']);
    $init_age       = trim($_POST['init_age']);
    $bmi_tcr        = trim($_POST['bmi_tcr']);
    $dayswait_alloc = trim($_POST['dayswait_alloc']);
    $kidney_cluster = trim($_POST['kidney_cluster']);
    $dgn_tcr        = (isset($_POST['dgn_tcr']) && $_POST['dgn_tcr'] !== "") ? trim($_POST['dgn_tcr']) : null;
    $hgt_cm_tcr     = trim($_POST['hgt_cm_tcr']);
    $wgt_kg_tcr     = trim($_POST['wgt_kg_tcr']);
    $gfr            = trim($_POST['gfr']);
    $on_dialysis    = trim($_POST['on_dialysis']);
    $blood_type     = trim($_POST['blood_type']);

    // Convert values to the correct types
    $init_age       = (int)$init_age;
    $bmi_tcr        = (float)$bmi_tcr;
    $dayswait_alloc = (int)$dayswait_alloc;
    $kidney_cluster = (int)$kidney_cluster;
    if (!is_null($dgn_tcr)) {
        $dgn_tcr = (float)$dgn_tcr;
    }
    $hgt_cm_tcr = (float)$hgt_cm_tcr;
    $wgt_kg_tcr = (float)$wgt_kg_tcr;
    $gfr        = (float)$gfr;
    
    // Convert "On Dialysis" from form value to boolean (1 for Yes, 0 for No)
    $on_dialysis_bool = ($on_dialysis === "Y") ? 1 : 0;
    
    // If blood type is empty, store as null
    if ($blood_type === "") {
        $blood_type = null;
    }
    
    // Ensure $patient_id is an integer
    $patient_id = (int)$patient_id;
    
    // Get the hospital id from the session
    $hospital_id = (int)$_SESSION['hospital_id'];
    
    // Generate a unique patient code for this recipient
    $patient_code = "Patient #" . rand(1000, 9999);
    // Insert the patient's medical information into the recipients table
    $stmt = $conn->prepare("INSERT INTO recipients (user_id, patient_code, init_age, bmi_tcr, dayswait_alloc, kidney_cluster, dgn_tcr, wgt_kg_tcr, hgt_cm_tcr, gfr, on_dialysis, blood_type, hospital_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    
    // Binding parameters:
    // Types: i (user_id), s (patient_code), i (init_age), d (bmi_tcr), i (dayswait_alloc), i (kidney_cluster),
    // d (dgn_tcr), d (wgt_kg_tcr), d (hgt_cm_tcr), d (gfr), i (on_dialysis), s (blood_type), i (hospital_id)
    $stmt->bind_param("isidiiddddisi", $patient_id, $patient_code, $init_age, $bmi_tcr, $dayswait_alloc, $kidney_cluster, $dgn_tcr, $wgt_kg_tcr, $hgt_cm_tcr, $gfr, $on_dialysis_bool, $blood_type, $hospital_id);
    
    if (!$stmt->execute()) {
        header("Location: ../templates/add_patient.php?status=error&message=Error adding patient medical information: " . $conn->error);
        exit();
    }
    
    $stmt->close();
    $conn->close();

    // Redirect to the hospital admin dashboard upon success
    header("Location: ../templates/hospital-admin.php?status=success&message=Patient added successfully.");
    exit();
}

// If the request is not a POST, redirect to the form
header("Location: ../templates/add_patient.php");
exit();
?>
