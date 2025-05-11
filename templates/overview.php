<?php
include_once '../config/connection.php';
session_start();

// Check if user is logged in and is a donor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'donor') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get donor information
$query = "SELECT 
            d.id, 
            d.user_id,
            d.blood_type, 
            d.organ_type,
            d.created_at, 
            u.name, 
            u.email 
          FROM donors d 
          JOIN users u ON d.user_id = u.id 
          WHERE d.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();
$stmt->close();

if (!$donor) {
    $error = "Donor profile not found. Please complete your profile first.";
}

$donor_id = $donor['id'];

// Get recent matches
$matches_query = "SELECT 
                    m.id, 
                    m.donor_id, 
                    m.recipient_id, 
                    m.match_score, 
                    m.created_at,
                    r.patient_code,  
                    r.blood_type as recipient_blood_type,
                    r.organ_type as recipient_organ_type
                  FROM matches m
                  JOIN recipients r ON m.recipient_id = r.id
                  WHERE m.donor_id = ?
                  ORDER BY m.created_at DESC
                  LIMIT 5";

$matches_stmt = $conn->prepare($matches_query);
$matches_stmt->bind_param("i", $donor_id);
$matches_stmt->execute();
$matches_result = $matches_stmt->get_result();
$recent_matches = [];
while ($match = $matches_result->fetch_assoc()) {
    $recent_matches[] = $match;
}
$matches_stmt->close();

// Get recent transplants
$transplants_query = "SELECT 
                        t.id, 
                        t.match_id, 
                        t.hospital_id, 
                        t.status, 
                        t.created_at,
                        m.donor_id,
                        m.recipient_id,
                        m.match_score,
                        r.patient_code,  
                        r.blood_type as recipient_blood_type,
                        r.organ_type as recipient_organ_type,
                        h.name as hospital_name
                      FROM transplants t
                      JOIN matches m ON t.match_id = m.id
                      JOIN recipients r ON m.recipient_id = r.id
                      JOIN hospitals h ON t.hospital_id = h.id
                      WHERE m.donor_id = ?
                      ORDER BY t.created_at DESC
                      LIMIT 5";

$transplants_stmt = $conn->prepare($transplants_query);
$transplants_stmt->bind_param("i", $donor_id);
$transplants_stmt->execute();
$transplants_result = $transplants_stmt->get_result();
$recent_transplants = [];
while ($transplant = $transplants_result->fetch_assoc()) {
    $recent_transplants[] = $transplant;
}
$transplants_stmt->close();

// Get total stats
$stats_query = "SELECT 
                 (SELECT COUNT(*) FROM matches WHERE donor_id = ?) as total_matches,
                 (SELECT COUNT(*) FROM transplants t JOIN matches m ON t.match_id = m.id WHERE m.donor_id = ?) as total_transplants";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("ii", $donor_id, $donor_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
$stats_stmt->close();

// Generate a unique patient code for each recipient
function generatePatientCode($recipient_id) {
    return 'P-' . substr(md5($recipient_id), 0, 6);
}

// Add this function near the top of your file, after the other database queries
// Function to get blockchain data for this donor
function getBlockchainData($key) {
    $scriptPath = "../blockchain/query-chaincode.sh";
    
    if (!file_exists($scriptPath)) {
        return ["error" => "Query script not found."];
    }
    
    if (!is_executable($scriptPath)) {
        chmod($scriptPath, 0755);
    }
    
    // Query for donor data on blockchain
    $cmd = escapeshellcmd($scriptPath) . " " . escapeshellarg($key);
    $output = [];
    $return_var = 0;
    exec($cmd . " 2>&1", $output, $return_var);
    
    // Debug: log the raw output for troubleshooting
    file_put_contents("/tmp/blockchain_debug.log", "CMD: " . $cmd . "\nOutput: " . print_r($output, true) . "\nReturn code: $return_var\n", FILE_APPEND);
    
    if ($return_var === 0 && !empty($output)) {
        $jsonResponse = implode("", $output);
        if (preg_match('/\{.*\}/s', $jsonResponse, $matches)) {
            $cleanedJson = $matches[0];
            $data = json_decode($cleanedJson, true);
        } else {
            $data = json_decode($jsonResponse, true);
        }
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }
    }
    
    return ["error" => "Could not retrieve blockchain data."];
}


// Get blockchain data for this donor
$blockchainData = getBlockchainData($user_id);

// Also get match data from blockchain if available
// Replace the current matchesBlockchainData retrieval code with this:
$matchesBlockchainData = [];
if (!empty($recent_matches)) {
  foreach ($recent_matches as $match) {
      if (isset($match['id'])) {
          $matchId = "match_" . $match['id'];
          
          // Log the query for debugging
          file_put_contents("/tmp/overviewx_debug.log", "Querying for match ID: $matchId\n", FILE_APPEND);
          
          $scriptPath = "../blockchain/query-chaincode.sh";
          if (!file_exists($scriptPath)) {
              file_put_contents("/tmp/overviewx_debug.log", "Script not found: $scriptPath\n", FILE_APPEND);
              continue;
          }
          
          if (!is_executable($scriptPath)) {
              chmod($scriptPath, 0755);
          }
          
          // Use the same command format as in query-blockchain.php
          $cmd = escapeshellcmd($scriptPath) . " " . escapeshellarg("ReadMatch") . " " . escapeshellarg($matchId);
          file_put_contents("/tmp/overviewx_debug.log", "Command: $cmd\n", FILE_APPEND);
          
          $output = [];
          $return_var = 0;
          exec($cmd . " 2>&1", $output, $return_var);
          
          file_put_contents("/tmp/overviewx_debug.log", "Return code: $return_var\n", FILE_APPEND);
          file_put_contents("/tmp/overviewx_debug.log", "Output: " . print_r($output, true) . "\n", FILE_APPEND);
          
          if ($return_var === 0 && !empty($output)) {
              $jsonResponse = implode("", $output);
              if (preg_match('/\{.*\}/s', $jsonResponse, $matches)) {
                  $cleanedJson = $matches[0];
                  file_put_contents("/tmp/overviewx_debug.log", "Extracted JSON: " . $cleanedJson . "\n", FILE_APPEND);
                  $matchData = json_decode($cleanedJson, true);
              } else {
                  $matchData = json_decode($jsonResponse, true);
              }
              
              if (json_last_error() === JSON_ERROR_NONE) {
                  $matchesBlockchainData[] = $matchData;
                  file_put_contents("/tmp/overviewx_debug.log", "Successfully parsed JSON data.\n", FILE_APPEND);
              } else {
                  file_put_contents("/tmp/overviewx_debug.log", "JSON parse error: " . json_last_error_msg() . "\n", FILE_APPEND);
              }
          } else {
              file_put_contents("/tmp/overviewx_debug.log", "Query failed with return code: $return_var\n", FILE_APPEND);
          }
      }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
  <link rel="manifest" href="../favicon_io/site.webmanifest">
  <title>LifeLink - Donor Overview</title>
  <style>
:root {
  --primary: #4070E0;
  --primary-hover: #3055B0;
  --primary-light: #EDF2FF;
  --primary-transparent: rgba(64, 112, 224, 0.1);
  --success: #34a853;
  --warning: #fbbc05;
  --danger: #ea4335;
  --gray-100: #f8f9fa;
  --gray-200: #EFF3FB;
  --gray-300: #e6e6e6;
  --gray-500: #6B7A99;
  --gray-900: #333;
  --white: #ffffff;
  --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
  --radius-sm: 12px;
  --radius-md: 16px;
  --radius-full: 50%;
  --transition-base: all 0.3s ease;
  --spacing-xs: 0.5rem;
  --spacing-sm: 1rem;
  --spacing-md: 1.5rem;
  --spacing-lg: 2rem;
  --spacing-xl: 3rem;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', sans-serif;
  min-height: 100vh;
  background-color: var(--gray-200);
  color: var(--gray-900);
  line-height: 1.6;
}

h1, h2, h3 {
  font-weight: 600;
  line-height: 1.2;
}
h1 {
  font-size: 1.75rem;
  color: var(--primary);
  margin-bottom: var(--spacing-sm);
}
h2 {
  font-size: 1.25rem;
  margin-bottom: var(--spacing-md);
  position: relative;
  padding-bottom: 10px;
}
h2::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: 0;
  height: 3px;
  width: 40px;
  background-color: var(--primary);
  border-radius: 2px;
}
h3 {
  font-size: 1.125rem;
}

p {
  margin-bottom: var(--spacing-sm);
}

.container {
  width: 100%;
  max-width: 1440px;
  margin: 0 auto;
  padding: 0 var(--spacing-sm);
}

/* Navbar */

.back-button {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--primary);
    text-decoration: none;
    padding: 8px 12px;
    border-radius: var(--radius-sm);
    transition: var(--transition-base);
}
.back-button:hover {
    background: var(--primary-transparent);
}

.back-button svg {
    width: 20px;
    height: 20px;
}
.navbar {
  padding: var(--spacing-md);
  background-color: var(--white);
  box-shadow: var(--shadow-sm);
  position: sticky;
  top: 0;
  z-index: 100;
}
.navbar-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--spacing-md);
}
.logo {
    flex: 1;
    text-align: center;
}
.logo img {
  height: 50px;
  width: auto;
}
.user-profile {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}
.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: var(--radius-full);
  background-color: var(--primary-light);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary);
  font-weight: 600;
}

/* Main Layout */
.overview-grid {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: var(--spacing-md);
  padding: var(--spacing-md) 0;
}
.full-width {
  grid-column: 1 / -1;
}

.card {
  background: var(--white);
  border-radius: var(--radius-md);
  padding: var(--spacing-md);
  box-shadow: var(--shadow-sm);
  transition: var(--transition-base);
  height: 100%;
}
.card:hover {
  box-shadow: var(--shadow-md);
}

/* Dashboard Stats */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: var(--spacing-md);
  margin-bottom: var(--spacing-md);
}
.stat-card {
  background: var(--white);
  border-radius: var(--radius-sm);
  padding: var(--spacing-md);
  box-shadow: var(--shadow-sm);
  text-align: center;
  position: relative;
  overflow: hidden;
  transition: var(--transition-base);
}
.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
}
.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 5px;
  height: 100%;
  background: var(--primary);
}
.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: var(--primary);
  margin-bottom: var(--spacing-xs);
}
.stat-label {
  color: var(--gray-500);
  font-size: 0.9rem;
}

/* Donor Profile Section */
.donor-info {
  display: flex;
  align-items: center;
  margin-bottom: var(--spacing-md);
}
.donor-avatar {
  width: 80px;
  height: 80px;
  border-radius: var(--radius-full);
  background-color: var(--primary);
  color: var(--white);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: 600;
  margin-right: var(--spacing-md);
}
.donor-details h3 {
  margin-bottom: var(--spacing-xs);
  color: var(--primary);
}
.donor-attributes {
  display: flex;
  flex-wrap: wrap;
  gap: var(--spacing-xs);
  margin-top: var(--spacing-xs);
}
.donor-attribute {
  background: var(--primary-light);
  color: var(--primary);
  font-size: 0.85rem;
  padding: 4px 12px;
  border-radius: 20px;
  font-weight: 500;
}

/* Match and Transplant Cards */
.match-row {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: var(--spacing-md);
  margin-top: var(--spacing-md);
}
.match-card {
  border-radius: var(--radius-sm);
  background: var(--white);
  padding: var(--spacing-md);
  box-shadow: var(--shadow-sm);
  transition: var(--transition-base);
  position: relative;
  overflow: hidden;
}
.match-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
}
.match-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background: var(--primary);
}
.match-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: var(--spacing-sm);
}
.match-date {
  font-size: 0.85rem;
  color: var(--gray-500);
}
.match-visualization {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: var(--spacing-md) 0;
  position: relative;
}
.match-donor-avatar, .match-recipient-avatar {
  width: 60px;
  height: 60px;
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  z-index: 2;
}
.match-donor-avatar {
  background-color: var(--primary);
  color: var(--white);
}
.match-recipient-avatar {
  background-color: var(--primary-light);
  color: var(--primary);
}
.match-connector {
  width: 100px;
  height: 2px;
  background: var(--primary);
  position: relative;
  margin: 0 var(--spacing-xs);
}
.match-connector::before {
  content: '→';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: var(--primary);
}
.match-compatibility {
  text-align: center;
  font-size: 1.2rem;
  font-weight: 600;
  color: var(--success);
  margin-bottom: var(--spacing-sm);
}
.match-details {
  background: var(--gray-100);
  border-radius: var(--radius-sm);
  padding: var(--spacing-sm);
}
.match-detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 5px;
  font-size: 0.9rem;
}
.match-detail-label {
  color: var(--gray-500);
}
.match-detail-value {
  font-weight: 500;
}
.transplant-hospital {
  margin-top: var(--spacing-sm);
  padding-top: var(--spacing-sm);
  border-top: 1px solid var(--gray-300);
}
.transplant-status {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
  margin-top: var(--spacing-xs);
}
.status-completed {
  background: rgba(52, 168, 83, 0.1);
  color: var(--success);
}
.status-pending {
  background: rgba(251, 188, 5, 0.1);
  color: var(--warning);
}
.blockchain-badge {
  display: flex;
  align-items: center;
  gap: var(--spacing-xs);
  margin-top: var(--spacing-sm);
  padding: var(--spacing-xs);
  border-radius: var(--radius-sm);
  background: rgba(64, 112, 224, 0.05);
  font-size: 0.85rem;
}
.blockchain-badge svg {
  width: 16px;
  height: 16px;
  fill: var(--primary);
}
.blockchain-hash {
  font-family: monospace;
  color: var(--primary);
  font-size: 0.8rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 180px;
}

/* Historical Timeline */
.timeline {
  position: relative;
  margin: var(--spacing-lg) 0;
  padding-left: 30px;
}
.timeline::before {
  content: '';
  position: absolute;
  top: 0;
  bottom: 0;
  left: 10px;
  width: 2px;
  background: var(--gray-300);
}
.timeline-item {
  position: relative;
  padding-bottom: var(--spacing-md);
}
.timeline-dot {
  position: absolute;
  left: -30px;
  width: 20px;
  height: 20px;
  border-radius: var(--radius-full);
  background: var(--primary);
  top: 0;
}
.timeline-content {
  background: var(--white);
  border-radius: var(--radius-sm);
  padding: var(--spacing-sm);
  box-shadow: var(--shadow-sm);
}
.timeline-date {
  color: var(--gray-500);
  font-size: 0.85rem;
  margin-bottom: var(--spacing-xs);
}

/* Blockchain Section */
/* Add these styles to the existing CSS in your page */

/* Enhanced Blockchain Section */
.blockchain-section {
  background: linear-gradient(135deg, #EDF2FF 0%, #ffffff 100%);
  border-radius: var(--radius-md);
  padding: var(--spacing-md);
  margin-top: var(--spacing-lg);
  position: relative;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}
.blockchain-logo {
  width: 40px;
  height: 40px;
  background: var(--primary);
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
}
.blockchain-logo svg {
  width: 24px;
  height: 24px;
  fill: white;
}
.blockchain-icon-bg {
  position: absolute;
  right: 20px;
  bottom: 20px;
  width: 150px;
  height: 150px;
  opacity: 0.05;
}
.blockchain-section h4 {
  margin: var(--spacing-md) 0 var(--spacing-sm);
  color: var(--primary);
  font-size: 1.1rem;
  position: relative;
  display: inline-block;
}

.blockchain-section h4::after {
  content: '';
  position: absolute;
  bottom: -5px;
  left: 0;
  width: 40px;
  height: 2px;
  background-color: var(--primary);
}

.blockchain-message {
  padding: var(--spacing-md);
  background: rgba(255, 255, 255, 0.7);
  border-radius: var(--radius-sm);
  text-align: center;
}

/* Blockchain Card for Donor */
.blockchain-card {
  background: white;
  border-radius: var(--radius-sm);
  padding: var(--spacing-sm);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: var(--spacing-md);
  border-left: 4px solid var(--primary);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.blockchain-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.blockchain-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--spacing-sm);
  padding-bottom: var(--spacing-xs);
  border-bottom: 1px solid var(--gray-200);
}

.blockchain-card-title {
  font-weight: 600;
  color: var(--primary);
}

.blockchain-timestamp {
  font-size: 0.8rem;
  color: var(--gray-500);
}

.blockchain-visualization {
  padding: var(--spacing-sm) 0;
}

.donor-blockchain-stats {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: var(--spacing-sm);
  margin: var(--spacing-sm) 0;
}

.blockchain-stat {
  background: var(--primary-light);
  border-radius: var(--radius-sm);
  padding: var(--spacing-sm);
  text-align: center;
  position: relative;
}

.blockchain-stat-value {
  font-size: 1.2rem;
  font-weight: 600;
  color: var(--primary);
}

.blockchain-stat-label {
  font-size: 0.8rem;
  color: var(--gray-500);
  margin-top: 4px;
}

.blockchain-buttons {
  margin-top: var(--spacing-sm);
  display: flex;
  justify-content: flex-end;
}

.blockchain-view-btn {
  display: inline-flex;
  align-items: center;
  background: var(--primary);
  color: white;
  font-size: 0.9rem;
  padding: 8px 16px;
  border-radius: 20px;
  text-decoration: none;
  transition: background 0.2s ease;
}

.blockchain-view-btn:hover {
  background: var(--primary-hover);
}

/* Blockchain Matches */
.blockchain-match-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: var(--spacing-md);
  margin-top: var(--spacing-sm);
}

.blockchain-match-card {
  background: white;
  border-radius: var(--radius-sm);
  padding: var(--spacing-sm);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  animation: fadeIn 0.5s ease-out forwards;
  opacity: 0;
}

.blockchain-match-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.blockchain-match-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--spacing-sm);
  padding-bottom: var(--spacing-xs);
  border-bottom: 1px solid var(--gray-200);
}

.blockchain-match-id {
  font-family: monospace;
  font-size: 0.9rem;
  color: var(--primary);
}

.blockchain-match-score {
  font-weight: 600;
  background: rgba(52, 168, 83, 0.1);
  color: var(--success);
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.85rem;
}

.blockchain-match-pair {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-sm) 0;
}

.blockchain-donor, .blockchain-recipient {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.blockchain-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1rem;
}

.blockchain-avatar.donor {
  background-color: var(--primary);
  color: white;
}

.blockchain-avatar.recipient {
  background-color: var(--primary-light);
  color: var(--primary);
}

.blockchain-id {
  font-family: monospace;
  font-size: 0.8rem;
  color: var(--gray-500);
}

.blockchain-match-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: var(--spacing-sm);
  padding-top: var(--spacing-xs);
  border-top: 1px solid var(--gray-200);
}

.blockchain-match-status {
  font-size: 0.85rem;
  padding: 4px 8px;
  border-radius: 12px;
}

.blockchain-match-status.completed {
  background: rgba(52, 168, 83, 0.1);
  color: var(--success);
}

.blockchain-match-status.pending {
  background: rgba(251, 188, 5, 0.1);
  color: var(--warning);
}

.blockchain-match-status.processing {
  background: rgba(64, 112, 224, 0.1);
  color: var(--primary);
}

.blockchain-view-link {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.85rem;
  color: var(--primary);
  text-decoration: none;
  transition: color 0.2s ease;
}

.blockchain-view-link:hover {
  color: var(--primary-hover);
  text-decoration: underline;
}

.blockchain-verification {
  margin-top: var(--spacing-md);
  background: rgba(64, 112, 224, 0.05);
  border-radius: var(--radius-sm);
  padding: var(--spacing-sm);
}

.blockchain-info {
  display: flex;
  align-items: flex-start;
  gap: var(--spacing-sm);
}

.blockchain-info svg {
  color: var(--primary);
  flex-shrink: 0;
  margin-top: 3px;
}

.blockchain-info p {
  font-size: 0.9rem;
  margin-bottom: 0;
  color: var(--gray-500);
}

.blockchain-no-matches {
  background: rgba(255, 255, 255, 0.7);
  padding: var(--spacing-md);
  border-radius: var(--radius-sm);
  text-align: center;
  color: var(--gray-500);
}

@media (max-width: 768px) {
  .blockchain-match-container {
    grid-template-columns: 1fr;
  }
  
  .donor-blockchain-stats {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .blockchain-card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .blockchain-match-pair {
    flex-direction: column;
    gap: var(--spacing-sm);
  }
  
  .blockchain-connector {
    transform: rotate(90deg);
    margin: 8px 0;
  }
  
  .blockchain-match-footer {
    flex-direction: column;
    gap: 12px;
    align-items: flex-start;
  }
}
/* Animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.animated-fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}
.animation-delay-1 {
  animation-delay: 0.1s;
}
.animation-delay-2 {
  animation-delay: 0.2s;
}
.animation-delay-3 {
  animation-delay: 0.3s;
}
.animation-delay-4 {
  animation-delay: 0.4s;
}
.animation-delay-5 {
  animation-delay: 0.5s;
}

/* Pulse Animation for Blockchain */
@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(64, 112, 224, 0.4); }
  70% { box-shadow: 0 0 0 10px rgba(64, 112, 224, 0); }
  100% { box-shadow: 0 0 0 0 rgba(64, 112, 224, 0); }
}
.pulse-animation {
  animation: pulse 2s infinite;
}

/* Mobile responsiveness */
@media (max-width: 1024px) {
  .overview-grid {
    grid-template-columns: 1fr;
  }
}
@media (max-width: 768px) {
  .match-row {
    grid-template-columns: 1fr;
  }
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  .donor-info {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
  .donor-avatar {
    margin-right: 0;
    margin-bottom: var(--spacing-sm);
  }
  .blockchain-record {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--spacing-xs);
  }
}
  </style>
</head>
<body>
<!-- Add Navigation Button in Navbar -->
<nav class="navbar">
    <div class="container navbar-content">
        <a href="match-page" class="back-button">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
            Back to Matches
        </a>
        <div class="logo">
            <img src="../assets/images/logo-removebg-preview.png" alt="LifeLink Logo">
        </div>
        <div class="user-profile">
            <span>Welcome, <?php echo $donor['name']; ?></span>
            <div class="user-avatar"><?php echo substr($donor['name'], 0, 1); ?></div>
        </div>
    </div>
</nav>
  
  <div class="container">
    <div class="full-width animated-fade-in">
      <h1>Donor Overview Dashboard</h1>
      <p>View your donation history, recent matches, and transplant records</p>
    </div>
    
    <!-- Stats Grid -->
    <div class="stats-grid animated-fade-in animation-delay-1">
      <div class="stat-card">
        <div class="stat-value"><?php echo $stats['total_matches']; ?></div>
        <div class="stat-label">Total Matches</div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?php echo $stats['total_transplants']; ?></div>
        <div class="stat-label">Total Transplants</div>
      </div>
      <div class="stat-card">
        <div class="stat-value"><?php echo number_format(($stats['total_transplants'] > 0 ? ($stats['total_transplants'] / $stats['total_matches']) * 100 : 0), 1); ?>%</div>
        <div class="stat-label">Success Rate</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">
          <?php 
            // Calculate days since last activity
            $days = 0;
            if (!empty($recent_transplants)) {
              $last_date = new DateTime($recent_transplants[0]['created_at']);
              $now = new DateTime();
              $days = $now->diff($last_date)->days;
            }
            echo $days;
          ?>
        </div>
        <div class="stat-label">Days Since Last Activity</div>
      </div>
    </div>
    
    <div class="overview-grid">
      <!-- Donor Profile Section -->
      <div class="card animated-fade-in animation-delay-2">
        <h2>Your Donor Profile</h2>
        <div class="donor-info">
          <div class="donor-avatar"><?php echo substr($donor['name'], 0, 1); ?></div>
          <div class="donor-details">
            <h3><?php echo $donor['name']; ?></h3>
            <p><?php echo $donor['email']; ?></p>
            <div class="donor-attributes">
              <span class="donor-attribute">Blood Type: <?php echo $donor['blood_type']; ?></span>
              <span class="donor-attribute">Organ: <?php echo $donor['organ_type']; ?></span>
              <span class="donor-attribute">Donor ID: <?php echo $donor['id']; ?></span>
            </div>
          </div>
        </div>
        
        <div class="timeline">
          <h3>Your Donation Journey</h3>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <div class="timeline-date"><?php echo date('F j, Y', strtotime($donor['created_at'])); ?></div>
              <p>Registered as a donor</p>
            </div>
          </div>
          
          <?php if (!empty($recent_matches)): ?>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <div class="timeline-date"><?php echo date('F j, Y', strtotime($recent_matches[0]['created_at'])); ?></div>
              <p>First match found</p>
            </div>
          </div>
          <?php endif; ?>
          
          <?php if (!empty($recent_transplants)): ?>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-content">
              <div class="timeline-date"><?php echo date('F j, Y', strtotime($recent_transplants[0]['created_at'])); ?></div>
              <p>Latest transplant completed</p>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
      
      <!-- Recent Transplants Section -->
      <div class="card animated-fade-in animation-delay-3">
        <h2>Recent Transplants</h2>
        <?php if (!empty($recent_transplants)): ?>
          <div class="match-row">
            <?php foreach ($recent_transplants as $transplant): ?>
              <div class="match-card">
                <div class="match-header">
                  <h3>Transplant #<?php echo $transplant['id']; ?></h3>
                  <div class="match-date"><?php echo date('M j, Y', strtotime($transplant['created_at'])); ?></div>
                </div>
                
                <div class="match-visualization">
                  <div class="match-donor-avatar"><?php echo substr($donor['name'], 0, 1); ?></div>
                  <div class="match-connector"></div>
                  <div class="match-recipient-avatar"><?php echo substr(generatePatientCode($transplant['recipient_id']), 0, 1); ?></div>
                </div>
                
                <div class="match-compatibility">
                <?php echo number_format($transplant['match_score'], 1); ?>% Match
                </div>
                
                <div class="match-details">
                  <div class="match-detail-row">
                    <span class="match-detail-label">Donor:</span>
                    <span class="match-detail-value"><?php echo $donor['name']; ?></span>
                  </div>
                  <div class="match-detail-row">
    <span class="match-detail-label">Recipient:</span>
    <span class="match-detail-value"><?php echo $transplant['patient_code']; ?></span>
</div>
                  <div class="match-detail-row">
                    <span class="match-detail-label">Blood Type:</span>
                    <span class="match-detail-value"><?php echo $transplant['recipient_blood_type']; ?></span>
                  </div>
                  <div class="match-detail-row">
                    <span class="match-detail-label">Organ:</span>
                    <span class="match-detail-value"><?php echo $transplant['recipient_organ_type']; ?></span>
                  </div>
                </div>
                
                <div class="transplant-hospital">
                  <strong>Hospital:</strong> <?php echo $transplant['hospital_name']; ?>
                  <div class="transplant-status status-<?php echo $transplant['status']; ?>">
                    Status: <?php echo ucfirst($transplant['status']); ?>
                  </div>
                </div>
                
                <?php if (!empty($transplant['blockchain_hash'])): ?>
                <div class="blockchain-badge">
                  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 0L7 4h4v8h2V4h4L12 0zm5 6v2h-3v2h3v2l4-3-4-3zm-10 4l-4 3 4 3v-2h3v-2H7v-2zm10 4v2h-4l5 4 5-4h-4v-2h-2zm-2 4H7l5 4 5-4h-7z"/>
                  </svg>
                  <span>Verified on Blockchain:</span>
                  <span class="blockchain-hash"><?php echo $transplant['blockchain_hash']; ?></span>
                </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p>No transplants recorded yet.</p>
        <?php endif; ?>
      </div>
      
      <!-- Recent Matches Section -->
      <div class="card animated-fade-in animation-delay-4">
        <h2>Recent Matches</h2>
        <?php if (!empty($recent_matches)): ?>
          <div class="match-row">
            <?php foreach ($recent_matches as $match): ?>
              <!-- Skip if this match is already in a transplant -->
              <?php
              $is_transplanted = false;
              foreach ($recent_transplants as $transplant) {
                if ($transplant['match_id'] == $match['id']) {
                  $is_transplanted = true;
                  break;
                }
              }
              if ($is_transplanted) continue;
              ?>
             <div class="match-card">
                <div class="match-header">
                  <h3>Match #<?php echo $match['id']; ?></h3>
                  <div class="match-date"><?php echo date('M j, Y', strtotime($match['created_at'])); ?></div>
                </div>
                
                <div class="match-visualization">
                  <div class="match-donor-avatar"><?php echo substr($donor['name'], 0, 1); ?></div>
                  <div class="match-connector"></div>
                  <div class="match-recipient-avatar"><?php echo substr(generatePatientCode($match['recipient_id']), 0, 1); ?></div>
                </div>
                
                <div class="match-compatibility">
                <?php echo number_format($transplant['match_score'], 1); ?>% Match
                </div>
                
                <div class="match-details">
                <div class="match-detail-row">
    <span class="match-detail-label">Recipient Code:</span>
    <span class="match-detail-value"><?php echo $match['patient_code']; ?></span>
</div>
                  <div class="match-detail-row">
                    <span class="match-detail-label">Blood Type:</span>
                    <span class="match-detail-value"><?php echo $match['recipient_blood_type']; ?></span>
                  </div>
                  <div class="match-detail-row">
                    <span class="match-detail-label">Organ Needed:</span>
                    <span class="match-detail-value"><?php echo $match['recipient_organ_type']; ?></span>
                  </div>
                  <div class="match-detail-row">
                    <span class="match-detail-label">Match Date:</span>
                    <span class="match-detail-value"><?php echo date('M j, Y H:i', strtotime($match['created_at'])); ?></span>
                  </div>
                </div>
                
                <div class="transplant-status status-pending">
                  Potential Match - Awaiting Confirmation
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p>No recent matches found.</p>
        <?php endif; ?>
      </div>
      
      <!-- Blockchain Integration Section (Placeholder) -->
      <!-- Below is the HTML for the blockchain section -->
<div class="card blockchain-section animated-fade-in animation-delay-5">
    <div class="blockchain-header">
        <div class="blockchain-logo pulse-animation">
            <svg viewBox="0 0 24 24">
                <path d="M12 0L7 4h4v8h2V4h4l-5-4zm5 6v2h-3v2h3v2l4-3-4-3zm-10 4l-4 3 4 3v-2h3v-2H7v-2zm10 4v2h-4l5 4 5-4h-4v-2h-2zm-2 4H7l5 4 5-4h-7z"/>
            </svg>
        </div>
        <h3>Blockchain Verification</h3>
    </div>
    
    <?php if (isset($blockchainData['error'])): ?>
        <p class="blockchain-message">Blockchain data not currently available. Please try again later.</p>
    <?php else: ?>
        <!-- Donor Information from Blockchain -->
        <div class="blockchain-donor-info">
            <h4>Your Medical Profile on Blockchain</h4>
            <div class="blockchain-card">
                <div class="blockchain-card-header">
                    <div class="blockchain-card-title">Donor ID: <?php echo htmlspecialchars($blockchainData['donorID'] ?? 'N/A'); ?></div>
                    <div class="blockchain-timestamp">
                        <?php if (isset($blockchainData['timestamp'])): ?>
                            Recorded: <?php echo date('Y-m-d H:i', intval($blockchainData['timestamp'])); ?>
                        <?php else: ?>
                            Timestamp not available
                        <?php endif; ?>
                    </div>
                </div>
                <div class="blockchain-visualization">
                    <div class="donor-blockchain-stats">
                        <div class="blockchain-stat">
                            <div class="blockchain-stat-value"><?php echo htmlspecialchars($blockchainData['bloodType'] ?? 'N/A'); ?></div>
                            <div class="blockchain-stat-label">Blood Type</div>
                        </div>
                        <?php if (isset($blockchainData['kidneyCluster'])): ?>
                        <div class="blockchain-stat">
                            <div class="blockchain-stat-value"><?php echo htmlspecialchars($blockchainData['kidneyCluster'] ?? 'N/A'); ?></div>
                            <div class="blockchain-stat-label">Kidney Cluster</div>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($blockchainData['gfr'])): ?>
                        <div class="blockchain-stat">
                            <div class="blockchain-stat-value"><?php echo htmlspecialchars($blockchainData['gfr'] ?? 'N/A'); ?></div>
                            <div class="blockchain-stat-label">GFR</div>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($blockchainData['bmiTcr'])): ?>
                        <div class="blockchain-stat">
                            <div class="blockchain-stat-value"><?php echo htmlspecialchars($blockchainData['bmiTcr'] ?? 'N/A'); ?></div>
                            <div class="blockchain-stat-label">BMI</div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
        
        <!-- Match Information from Blockchain -->
        <?php if (!empty($matchesBlockchainData)): ?>
            <div class="blockchain-matches">
                <h4>Match Records on Blockchain</h4>
                <div class="blockchain-match-container">
                    <?php foreach ($matchesBlockchainData as $index => $matchData): ?>
                        <div class="blockchain-match-card animation-delay-<?php echo ($index % 5) + 1; ?>">
                            <div class="blockchain-match-header">
                                <div class="blockchain-match-id">Match ID: <?php echo htmlspecialchars($matchData['matchID'] ?? 'N/A'); ?></div>
                                <div class="blockchain-match-score"><?php echo htmlspecialchars($matchData['matchScore'] ?? 'N/A'); ?>% Match</div>
                            </div>
                            
                            <div class="blockchain-match-pair">
                                <div class="blockchain-donor">
                                    <div class="blockchain-avatar donor">D</div>
                                    <div class="blockchain-id"><?php echo htmlspecialchars($matchData['donorID'] ?? 'N/A'); ?></div>
                                </div>
                                
                                <div class="blockchain-connector">
                                    <svg width="40" height="15">
                                        <line x1="0" y1="7.5" x2="40" y2="7.5" stroke="#4070E0" stroke-width="2"/>
                                        <polygon points="40,7.5 35,5 35,10" fill="#4070E0"/>
                                    </svg>
                                </div>
                                
                                <div class="blockchain-recipient">
                                    <div class="blockchain-avatar recipient">R</div>
                                    <div class="blockchain-id"><?php echo htmlspecialchars($matchData['recipientID'] ?? 'N/A'); ?></div>
                                </div>
                            </div>
                            
                            <div class="blockchain-match-footer">
                                <div class="blockchain-match-status <?php echo strtolower($matchData['status'] ?? 'pending'); ?>">
                                    Status: <?php echo htmlspecialchars($matchData['status'] ?? 'Pending'); ?>
                                </div>
                                <a href="../blockchain/view_blockchain.php?match_id=<?php echo $matchData['matchID']; ?>" class="blockchain-view-link">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="blockchain-no-matches">
                <p>No match records found on the blockchain yet.</p>
            </div>
        <?php endif; ?>
        
        <div class="blockchain-verification">
            <div class="blockchain-info">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 16v-4"></path>
                    <path d="M12 8h.01"></path>
                </svg>
                <p>All medical data is securely stored and verified on our healthcare blockchain. 
                This ensures data integrity, transparency, and immutability throughout the organ donation process.</p>
            </div>
        </div>
    <?php endif; ?>
    
    <svg class="blockchain-icon-bg" viewBox="0 0 24 24">
        <path d="M12 0L7 4h4v8h2V4h4l-5-4zm5 6v2h-3v2h3v2l4-3-4-3zm-10 4l-4 3 4 3v-2h3v-2H7v-2zm10 4v2h-4l5 4 5-4h-4v-2h-2zm-2 4H7l5 4 5-4h-7z"/>
    </svg>
</div>
    </div>
  </div>
</body>
</html>