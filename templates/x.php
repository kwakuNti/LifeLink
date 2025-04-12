//x.php
<?php
session_start();
include_once '../config/connection.php';
include '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../templates/login.php?status=error&message=Please+log+in+first.");
    exit();
}

// Default to the logged-in user ID for donor query, unless a donor_id is specified.
$donorId = isset($_GET['donor_id']) ? $_GET['donor_id'] : $_SESSION['user_id'];
// Optionally, check for a match_id parameter.
$matchId = isset($_GET['match_id']) ? $_GET['match_id'] : null;

// Create a log file for debugging.
$logFile = "/tmp/blockchain_query_" . time() . ".log";
file_put_contents($logFile, "=== LIFELINK BLOCKCHAIN QUERY DEBUG LOG ===\n");
file_put_contents($logFile, "Timestamp: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
if ($matchId) {
    file_put_contents($logFile, "Querying for Match ID: " . $matchId . "\n\n", FILE_APPEND);
} else {
    file_put_contents($logFile, "Querying for Donor ID: " . $donorId . "\n\n", FILE_APPEND);
}

// Path to the query script.
$scriptPath = "/Applications/MAMP/htdocs/Lifelink/blockchain/query-chaincode.sh";

// Check if the script exists and is executable.
if (!file_exists($scriptPath)) {
    file_put_contents($logFile, "ERROR: Script file does not exist at: $scriptPath\n", FILE_APPEND);
    $error = "Query script not found.";
} else {
    if (!is_executable($scriptPath)) {
        chmod($scriptPath, 0755);
    }
    
    // Construct the command.
    if ($matchId) {
        $cmd = escapeshellcmd($scriptPath) . " " . escapeshellarg("ReadMatch") . " " . escapeshellarg($matchId);
    } else {
        $cmd = escapeshellcmd($scriptPath) . " " . escapeshellarg($donorId);
    }
    file_put_contents($logFile, "Command: $cmd\n", FILE_APPEND);

    // Execute the command.
    $output = [];
    $return_var = 0;
    exec($cmd . " 2>&1", $output, $return_var);

    // Log results.
    file_put_contents($logFile, "Return code: $return_var\n", FILE_APPEND);
    file_put_contents($logFile, "Output: " . print_r($output, true) . "\n", FILE_APPEND);

    if ($return_var === 0 && !empty($output)) {
        $jsonResponse = implode("", $output);
        if (preg_match('/\{.*\}/s', $jsonResponse, $matches)) {
            $cleanedJson = $matches[0];
            file_put_contents($logFile, "Extracted JSON: " . $cleanedJson . "\n", FILE_APPEND);
            $data = json_decode($cleanedJson, true);
        } else {
            $data = json_decode($jsonResponse, true);
        }
        
        if (json_last_error() === JSON_ERROR_NONE) {
            $queryResult = $data;
            file_put_contents($logFile, "Successfully parsed JSON data.\n", FILE_APPEND);
        } else {
            $error = "Failed to parse blockchain response: " . json_last_error_msg();
            file_put_contents($logFile, "JSON parse error: " . json_last_error_msg() . "\n", FILE_APPEND);
        }
    } else {
        $error = "Failed to query the blockchain.";
        file_put_contents($logFile, "Query failed with return code: $return_var\n", FILE_APPEND);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blockchain Query Result</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        table, th, td { border: 1px solid #ccc; }
        th, td { padding: 0.5rem; text-align: left; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Blockchain Query Result</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif (isset($queryResult)): ?>
            <?php if (isset($queryResult['donorID'])): ?>
                <div class="card">
                    <div class="card-header">
                        <h2>Donor Medical Information (ID: <?php echo htmlspecialchars($queryResult['donorID']); ?>)</h2>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tr>
                                <th>Blood Type:</th>
                                <td><?php echo htmlspecialchars($queryResult['bloodType'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Age:</th>
                                <td><?php echo htmlspecialchars($queryResult['init_age'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>BMI:</th>
                                <td><?php echo htmlspecialchars($queryResult['bmi_tcr'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Days on Waiting List:</th>
                                <td><?php echo htmlspecialchars($queryResult['dayswait_alloc'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Kidney Cluster:</th>
                                <td><?php echo htmlspecialchars($queryResult['kidney_cluster'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Diagnosis Code:</th>
                                <td><?php echo htmlspecialchars($queryResult['dgn_tcr'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Weight (kg):</th>
                                <td><?php echo htmlspecialchars($queryResult['wgt_kg_tcr'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Height (cm):</th>
                                <td><?php echo htmlspecialchars($queryResult['hgt_cm_tcr'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>GFR:</th>
                                <td><?php echo htmlspecialchars($queryResult['gfr'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>On Dialysis:</th>
                                <td><?php echo (isset($queryResult['on_dialysis']) && intval($queryResult['on_dialysis']) === 1) ? 'Yes' : 'No'; ?></td>
                            </tr>
                            <tr>
                                <th>File Reference:</th>
                                <td><?php echo htmlspecialchars($queryResult['fileReference'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Recorded On:</th>
                                <td>
                                    <?php 
                                    if (isset($queryResult['timestamp'])) {
                                        echo date('Y-m-d H:i:s', intval($queryResult['timestamp']));
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php elseif (isset($queryResult['matchID'])): ?>
                <div class="card">
                    <div class="card-header">
                        <h2>Match Record (ID: <?php echo htmlspecialchars($queryResult['matchID']); ?>)</h2>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tr>
                                <th>Donor ID:</th>
                                <td><?php echo htmlspecialchars($queryResult['donorID'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Recipient ID:</th>
                                <td><?php echo htmlspecialchars($queryResult['recipientID'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Match Score:</th>
                                <td><?php echo htmlspecialchars($queryResult['matchScore'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td><?php echo htmlspecialchars($queryResult['status'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Recorded On:</th>
                                <td>
                                    <?php 
                                    if (isset($queryResult['timestamp'])) {
                                        echo date('Y-m-d H:i:s', intval($queryResult['timestamp']));
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <h3>Raw Blockchain Data</h3>
                <pre><?php echo json_encode($queryResult, JSON_PRETTY_PRINT); ?></pre>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                No data found.
            </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <a href="../templates/donor_medical_info.php" class="btn btn-primary">Back to Medical Form</a>
        </div>
    </div>
</body>
</html>
