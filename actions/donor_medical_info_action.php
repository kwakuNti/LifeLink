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

// Create log file at the beginning
$logFile = "/tmp/fabric_debug_".time().".log";
file_put_contents($logFile, "=== LIFELINK BLOCKCHAIN INTEGRATION DEBUG LOG ===\n");
file_put_contents($logFile, "Timestamp: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
file_put_contents($logFile, "User ID: " . $userId . "\n\n", FILE_APPEND);

// 1. Gather posted form data (required + optional)
$initAge       = isset($_POST['init_age']) ? (int)$_POST['init_age'] : 0;
$bmiTcr        = isset($_POST['bmi_tcr']) ? (float)$_POST['bmi_tcr'] : 0.0;
$dayswaitAlloc = isset($_POST['dayswait_alloc']) ? (int)$_POST['dayswait_alloc'] : 0;
$kidneyCluster = isset($_POST['kidney_cluster']) ? (int)$_POST['kidney_cluster'] : 0;

// Optional fields
$dgnTcr   = isset($_POST['dgn_tcr']) ? (float)$_POST['dgn_tcr'] : 0.0;
$wgtKgTcr = isset($_POST['wgt_kg_tcr']) ? (float)$_POST['wgt_kg_tcr'] : 0.0;
$hgtCmTcr = isset($_POST['hgt_cm_tcr']) ? (float)$_POST['hgt_cm_tcr'] : 0.0;
$gfr      = isset($_POST['gfr']) ? (float)$_POST['gfr'] : 0.0;

// On Dialysis (Y/N => boolean)
$onDialysis     = $_POST['on_dialysis'] ?? 'N';
$onDialysisBool = ($onDialysis === 'Y') ? 1 : 0;

// Blood Type (A, B, AB, O)
$bloodType = $_POST['blood_type'] ?? '';

// Log form data
file_put_contents($logFile, "FORM DATA:\n", FILE_APPEND);
file_put_contents($logFile, "Age: $initAge\n", FILE_APPEND);
file_put_contents($logFile, "BMI: $bmiTcr\n", FILE_APPEND);
file_put_contents($logFile, "Days on Waiting List: $dayswaitAlloc\n", FILE_APPEND);
file_put_contents($logFile, "Kidney Cluster: $kidneyCluster\n", FILE_APPEND);
file_put_contents($logFile, "Blood Type: $bloodType\n", FILE_APPEND);
file_put_contents($logFile, "On Dialysis: $onDialysis\n\n", FILE_APPEND);

if (empty($bloodType)) {
    file_put_contents($logFile, "ERROR: Blood type is required.\n", FILE_APPEND);
    header("Location: ../templates/donor_medical_info?status=error&message=Blood+type+is+required.");
    exit();
}

// Basic validation for required fields
if ($initAge <= 0 || $bmiTcr <= 0.0 || $dayswaitAlloc < 0) {
    file_put_contents($logFile, "ERROR: Missing or invalid required fields.\n", FILE_APPEND);
    header("Location: ../templates/donor_medical_info?status=error&message=Missing+or+invalid+required+fields.");
    exit();
}

// Set fileRef to an empty string as the file upload part is removed.
$fileRef = "";

// Log database operations
file_put_contents($logFile, "DATABASE OPERATIONS:\n", FILE_APPEND);

// 3. FIRST check if donor already exists in blockchain
// Invoke the shell script with a query function to check if record exists
$checkScriptPath = "../blockchain/query-chaincode.sh"; // Path to query script
file_put_contents($logFile, "Check script path: $checkScriptPath\n", FILE_APPEND);

// Check if query script exists
if (!file_exists($checkScriptPath)) {
    file_put_contents($logFile, "ERROR: Query script file does not exist at: $checkScriptPath\n", FILE_APPEND);
    // Create a basic query script if it doesn't exist
    $queryScriptContent = "#!/bin/bash\n";
    $queryScriptContent .= "echo \"Querying for donor record: \$1\"\n";
    $queryScriptContent .= "# Execute actual chaincode query here\n";
    $queryScriptContent .= "# For now, return mock response\n";
    $queryScriptContent .= "exit 0\n";
    file_put_contents($checkScriptPath, $queryScriptContent);
    chmod($checkScriptPath, 0755);
    file_put_contents($logFile, "Created temporary query script for demonstration.\n", FILE_APPEND);
}

// Query command to check if donor exists
$checkCmd = escapeshellcmd($checkScriptPath) . " " . escapeshellarg("QueryMedicalInfo") . " " . 
            escapeshellarg($userId);

file_put_contents($logFile, "\nCHECK COMMAND EXECUTION:\n", FILE_APPEND);
file_put_contents($logFile, "Full check command: $checkCmd\n", FILE_APPEND);

// Execute the check script
$checkOutput = [];
$checkReturnVar = 255;

try {
    exec($checkCmd . " 2>&1", $checkOutput, $checkReturnVar);
    file_put_contents($logFile, "Check command executed.\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents($logFile, "CHECK EXEC ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
}

// Log the output and return code
file_put_contents($logFile, "Check command output:\n" . print_r($checkOutput, true) . "\n", FILE_APPEND);
file_put_contents($logFile, "Check return code: $checkReturnVar\n", FILE_APPEND);

// Determine if record exists in blockchain
$recordExistsInBlockchain = false;
if ($checkReturnVar === 0) {
    // Check for "not found" in output or other indicators
    $notFoundIndicators = ['not found', 'does not exist', '{}', 'null', 'nil'];
    $foundRecord = true;
    
    foreach ($notFoundIndicators as $indicator) {
        if (stripos(implode(' ', $checkOutput), $indicator) !== false) {
            $foundRecord = false;
            break;
        }
    }
    
    $recordExistsInBlockchain = $foundRecord;
}

file_put_contents($logFile, "Record exists in blockchain: " . ($recordExistsInBlockchain ? "Yes" : "No") . "\n\n", FILE_APPEND);

// If record exists in blockchain, do not proceed with any updates
if ($recordExistsInBlockchain) {
    file_put_contents($logFile, "IMMUTABLE RECORD: Donor information already exists on blockchain. No changes allowed.\n", FILE_APPEND);
    header("Location: ../templates/donor_medical_info?status=warning&message=Your+medical+information+is+already+recorded+on+the+blockchain+and+cannot+be+modified.+Please+contact+system+administrator+for+assistance+if+needed.");
    exit();
}

// 2. Insert or update donor medical information in your SQL database
$dbSuccessful = false;
try {
    $stmt = $conn->prepare("SELECT id FROM donors WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        file_put_contents($logFile, "Updating existing donor record for user ID: $userId\n", FILE_APPEND);
        // Update existing donor record
        $stmt->close();
        $sql = "UPDATE donors SET 
                blood_type = ?, init_age = ?, bmi_tcr = ?, 
                dayswait_alloc = ?, kidney_cluster = ?, dgn_tcr = ?,
                wgt_kg_tcr = ?, hgt_cm_tcr = ?, gfr = ?, on_dialysis = ?
                WHERE user_id = ?";
        $stmtUpdate = $conn->prepare($sql);
        $stmtUpdate->bind_param(
            "sidiidddiii",
            $bloodType, $initAge, $bmiTcr, $dayswaitAlloc, $kidneyCluster,
            $dgnTcr, $wgtKgTcr, $hgtCmTcr, $gfr, $onDialysisBool, $userId
        );
        $stmtUpdate->execute();
        file_put_contents($logFile, "Database update completed. Affected rows: " . $stmtUpdate->affected_rows . "\n", FILE_APPEND);
        $stmtUpdate->close();
        $dbSuccessful = true;
    } else {
        file_put_contents($logFile, "Inserting new donor record for user ID: $userId\n", FILE_APPEND);
        // Insert new donor record
        $stmt->close();
        $sql = "INSERT INTO donors (
                user_id, blood_type, init_age, bmi_tcr, dayswait_alloc,
                kidney_cluster, dgn_tcr, wgt_kg_tcr, hgt_cm_tcr, gfr, on_dialysis
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtInsert = $conn->prepare($sql);
        $stmtInsert->bind_param(
            "isidiidddii",
            $userId, $bloodType, $initAge, $bmiTcr, $dayswaitAlloc,
            $kidneyCluster, $dgnTcr, $wgtKgTcr, $hgtCmTcr, $gfr, $onDialysisBool
        );
        $stmtInsert->execute();
        file_put_contents($logFile, "Database insert completed. Affected rows: " . $stmtInsert->affected_rows . "\n", FILE_APPEND);
        $stmtInsert->close();
        $dbSuccessful = true;
    }
} catch (Exception $e) {
    file_put_contents($logFile, "DATABASE ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    $dbSuccessful = false;
}

$conn->close();
file_put_contents($logFile, "Database connection closed.\n\n", FILE_APPEND);

// BLOCKCHAIN INTEGRATION
file_put_contents($logFile, "BLOCKCHAIN INTEGRATION:\n", FILE_APPEND);

// Invoke the shell script to store donor medical information on the blockchain
$scriptPath = "../blockchain/invoke-chaincode.sh"; // Full path to script
file_put_contents($logFile, "Script path: $scriptPath\n", FILE_APPEND);

// Check if script exists and is executable
if (!file_exists($scriptPath)) {
    file_put_contents($logFile, "ERROR: Script file does not exist at: $scriptPath\n", FILE_APPEND);
    header("Location: ../templates/donor_medical_info?status=error&message=Blockchain+script+not+found.");
    exit();
} else {
    file_put_contents($logFile, "Script exists. Checking permissions...\n", FILE_APPEND);
    $perms = fileperms($scriptPath);
    file_put_contents($logFile, "Script permissions: " . decoct($perms & 0777) . "\n", FILE_APPEND);
    
    // Check if script is executable
    if (!is_executable($scriptPath)) {
        file_put_contents($logFile, "WARNING: Script is not executable. Attempting to make it executable...\n", FILE_APPEND);
        chmod($scriptPath, 0755);
        file_put_contents($logFile, "New permissions: " . decoct(fileperms($scriptPath) & 0777) . "\n", FILE_APPEND);
    }
}

// Get environment information
file_put_contents($logFile, "\nENVIRONMENT INFORMATION:\n", FILE_APPEND);
file_put_contents($logFile, "Current user: " . exec('whoami 2>&1') . "\n", FILE_APPEND);
file_put_contents($logFile, "Current directory: " . exec('pwd 2>&1') . "\n", FILE_APPEND);
file_put_contents($logFile, "PATH environment variable: " . getenv('PATH') . "\n", FILE_APPEND);
file_put_contents($logFile, "Peer binary location: " . exec('which peer 2>&1') . "\n", FILE_APPEND);
file_put_contents($logFile, "PHP Server variables: " . print_r($_SERVER, true) . "\n", FILE_APPEND);

// Construct command with arguments and add full path
$cmd = escapeshellcmd($scriptPath) . " " . escapeshellarg("CreateMedicalInfo") . " " .
       escapeshellarg($userId) . " " . 
       escapeshellarg($bloodType) . " " . 
       escapeshellarg($initAge) . " " . 
       escapeshellarg($bmiTcr) . " " . 
       escapeshellarg($dayswaitAlloc) . " " . 
       escapeshellarg($kidneyCluster) . " " . 
       escapeshellarg($dgnTcr) . " " . 
       escapeshellarg($wgtKgTcr) . " " . 
       escapeshellarg($hgtCmTcr) . " " . 
       escapeshellarg($gfr) . " " . 
       escapeshellarg($onDialysisBool) . " " . 
       escapeshellarg($fileRef);

file_put_contents($logFile, "\nCOMMAND EXECUTION:\n", FILE_APPEND);
file_put_contents($logFile, "Full command: $cmd\n", FILE_APPEND);

// Execute the script
$output = [];
$return_var = 255; // Set a default error code

try {
    exec($cmd . " 2>&1", $output, $return_var);
    file_put_contents($logFile, "Command executed.\n", FILE_APPEND);
} catch (Exception $e) {
    file_put_contents($logFile, "EXEC ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
}

// Log the output and return code
file_put_contents($logFile, "Command output:\n" . print_r($output, true) . "\n", FILE_APPEND);
file_put_contents($logFile, "Return code: $return_var\n", FILE_APPEND);

// Check for specific "already exists" error in blockchain output
$alreadyExistsError = false;
foreach ($output as $line) {
    if (strpos($line, "already exists") !== false) {
        $alreadyExistsError = true;
        break;
    }
}

// Redirect based on result - handle the "already exists" error specifically
if ($return_var === 0) {
    file_put_contents($logFile, "\nSUCCESS: Redirecting to map.php\n", FILE_APPEND);
    header("Location: ../templates/map.php?status=success&message=Medical+information+submitted+successfully+to+the+blockchain.+This+information+is+now+immutable.");
} else if ($alreadyExistsError) {
    file_put_contents($logFile, "\nERROR: Donor already exists in blockchain\n", FILE_APPEND);
    header("Location: ../templates/donor_medical_info?status=warning&message=Your+medical+information+is+already+recorded+on+the+blockchain+and+cannot+be+modified.+Please+contact+the+system+administrator+if+you+need+assistance.");
} else {
    file_put_contents($logFile, "\nERROR: Redirecting to donor_medical_info.php with error message\n", FILE_APPEND);
    header("Location: ../templates/donor_medical_info?status=error&message=Failed+to+store+medical+information+on+the+blockchain.+Please+try+again+or+contact+support.");
}
exit();
?>