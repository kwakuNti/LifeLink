<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config/connection.php';
include '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../templates/login.php?status=error&message=Please+log+in+first.");
    exit();
}

$userId = $_SESSION['user_id'];

// Create log file
$logFile = "/tmp/fabric_debug_" . time() . ".log";
file_put_contents($logFile, "=== LIFELINK BLOCKCHAIN INTEGRATION DEBUG ===\n");
file_put_contents($logFile, "Timestamp: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
file_put_contents($logFile, "User ID: $userId\n\n", FILE_APPEND);

// Gather form inputs
$initAge       = (int)($_POST['init_age'] ?? 0);
$bmiTcr        = (float)($_POST['bmi_tcr'] ?? 0);
$dayswaitAlloc = (int)($_POST['dayswait_alloc'] ?? 0);
$kidneyCluster = (int)($_POST['kidney_cluster'] ?? 0);
$dgnTcr        = (float)($_POST['dgn_tcr'] ?? 0);
$wgtKgTcr      = (float)($_POST['wgt_kg_tcr'] ?? 0);
$hgtCmTcr      = (float)($_POST['hgt_cm_tcr'] ?? 0);
$gfr           = (float)($_POST['gfr'] ?? 0);
$onDialysis    = $_POST['on_dialysis'] ?? 'N';
$onDialysisBool= ($onDialysis === 'Y') ? 1 : 0;
$bloodType     = $_POST['blood_type'] ?? '';

// Log form data
file_put_contents($logFile, "FORM DATA:\n", FILE_APPEND);
file_put_contents($logFile, "Age: $initAge\nBMI: $bmiTcr\nDays Wait: $dayswaitAlloc\nCluster: $kidneyCluster\nBlood Type: $bloodType\nOn Dialysis: $onDialysis\n\n", FILE_APPEND);

// Validate required
if (empty($bloodType) || $initAge <= 0 || $bmiTcr <= 0 || $dayswaitAlloc < 0) {
    file_put_contents($logFile, "ERROR: Missing or invalid required fields.\n", FILE_APPEND);
    header("Location: ../templates/donor_medical_info?status=error&message=Missing+or+invalid+fields");
    exit();
}

// Query existing blockchain record by donor ID
$scriptPath = realpath(__DIR__ . '/../blockchain/query-chaincode.sh');
file_put_contents($logFile, "Query script path: $scriptPath\n", FILE_APPEND);
if (!file_exists($scriptPath) || !is_executable($scriptPath)) {
    file_put_contents($logFile, "ERROR: query-chaincode.sh not found or not executable.\n", FILE_APPEND);
    // Let SQL update still proceed if needed
    $recordExistsInBlockchain = false;
} else {
    $checkCmd = escapeshellcmd($scriptPath) . ' ' . escapeshellarg($userId);
    file_put_contents($logFile, "CHECK CMD: $checkCmd\n", FILE_APPEND);
    exec($checkCmd . ' 2>&1', $output, $ret);
    file_put_contents($logFile, "Output: " . print_r($output, true) . "Return: $ret\n", FILE_APPEND);
    $recordExistsInBlockchain = ($ret === 0 && !preg_grep('/not exist|error|failed/i', $output));
}

file_put_contents($logFile, "Record exists on blockchain: " . ($recordExistsInBlockchain ? 'Yes' : 'No') . "\n\n", FILE_APPEND);

if ($recordExistsInBlockchain) {
    header("Location: ../templates/donor_medical_info?status=warning&message=Medical+info+already+on+blockchain");
    exit();
}

// Insert or update SQL donor record
try {
    $stmt = $conn->prepare("SELECT id FROM donors WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $upd = $conn->prepare(
            "UPDATE donors SET blood_type=?, init_age=?, bmi_tcr=?, dayswait_alloc=?, kidney_cluster=?, dgn_tcr=?, wgt_kg_tcr=?, hgt_cm_tcr=?, gfr=?, on_dialysis=? WHERE user_id=?"
        );
        $upd->bind_param("sidiidddiii", $bloodType, $initAge, $bmiTcr, $dayswaitAlloc, $kidneyCluster,
                                                          $dgnTcr, $wgtKgTcr, $hgtCmTcr, $gfr, $onDialysisBool, $userId);
        $upd->execute();
        $upd->close();
        file_put_contents($logFile, "SQL update OK.\n", FILE_APPEND);
    } else {
        $stmt->close();
        $ins = $conn->prepare(
            "INSERT INTO donors (user_id, blood_type, init_age, bmi_tcr, dayswait_alloc, kidney_cluster, dgn_tcr, wgt_kg_tcr, hgt_cm_tcr, gfr, on_dialysis)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $ins->bind_param("isidiidddii", $userId, $bloodType, $initAge, $bmiTcr, $dayswaitAlloc,
                                                     $kidneyCluster, $dgnTcr, $wgtKgTcr, $hgtCmTcr, $gfr, $onDialysisBool);
        $ins->execute();
        $ins->close();
        file_put_contents($logFile, "SQL insert OK.\n", FILE_APPEND);
    }
    $conn->close();
} catch (Exception $e) {
    file_put_contents($logFile, "SQL ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    header("Location: ../templates/donor_medical_info?status=error&message=Database+error");
    exit();
}

// Invoke blockchain write
$invokeScript = realpath(__DIR__ . '/../blockchain/invoke-chaincode.sh');
file_put_contents($logFile, "Invoke script: $invokeScript\n", FILE_APPEND);
if (file_exists($invokeScript) && is_executable($invokeScript)) {
    $args = [
        'CreateMedicalInfo', $userId, $bloodType, $initAge, $bmiTcr,
        $dayswaitAlloc, $kidneyCluster, $dgnTcr, $wgtKgTcr, $hgtCmTcr,
        $gfr, $onDialysisBool, ''
    ];
    $cmd = escapeshellcmd($invokeScript) . ' ' . implode(' ', array_map('escapeshellarg', $args));
    file_put_contents($logFile, "INVOKE CMD: $cmd\n", FILE_APPEND);
    exec($cmd . ' 2>&1', $out2, $ret2);
    file_put_contents($logFile, "Output: " . print_r($out2, true) . "Return: $ret2\n", FILE_APPEND);
    if ($ret2 === 0) {
        header("Location: ../templates/map.php?status=success&message=Medical+info+submitted");
    } else {
        header("Location: ../templates/donor_medical_info?status=error&message=Blockchain+invoke+failed");
    }
} else {
    header("Location: ../templates/donor_medical_info?status=error&message=Invoke+script+missing");
}
exit();
