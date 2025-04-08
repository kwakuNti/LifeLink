<?php
session_start();
include '../config/connection.php';

// Ensure the hospital is logged in
if (!isset($_SESSION['hospital_id'])) {
    header("Location: ../templates/hospital_login.php?status=error&message=Please log in first.");
    exit();
}

$hospital_id = (int)$_SESSION['hospital_id'];

// Query patients added by this hospital by joining recipients and users tables
$stmt = $conn->prepare("SELECT u.name, u.email, r.location, r.created_at, r.patient_code, r.blood_type, r.on_dialysis 
                        FROM recipients r 
                        JOIN users u ON r.user_id = u.id 
                        WHERE r.hospital_id = ? 
                        ORDER BY r.created_at DESC");
$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$result = $stmt->get_result();
$patients = [];
while ($row = $result->fetch_assoc()){
    $patients[] = $row;
}

// Get hospital name
$stmt = $conn->prepare("SELECT name FROM hospitals WHERE id = ?");
$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$hospital_result = $stmt->get_result();
$hospital_name = $hospital_result->fetch_assoc()['name'] ?? 'Hospital Dashboard';

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Patient Dashboard - <?php echo htmlspecialchars($hospital_name); ?></title>
  <link rel="stylesheet" href="../public/css/snackbar.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary: #1e3a8a;
      --secondary: #3b82f6;
      --accent: #60a5fa;
      --light: #f0f9ff;
      --dark: #1e293b;
      --success: #10b981;
      --warning: #f59e0b;
      --danger: #ef4444;
      --gray: #6b7280;
      --card-bg: #ffffff;
      --body-bg: #f1f5f9;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--body-bg);
      color: var(--dark);
      line-height: 1.6;
    }
    
    .container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 20px;
    }
    
    header {
      background-color: var(--primary);
      color: white;
      padding: 20px 0;
      margin-bottom: 30px;
      border-radius: 0 0 10px 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .header-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px;
    }
    
    .hospital-info h1 {
      font-size: 1.8rem;
      margin-bottom: 5px;
    }
    
    .hospital-info p {
      font-size: 0.9rem;
      opacity: 0.8;
    }
    
    .dashboard-stats {
      display: flex;
      gap: 20px;
      margin-bottom: 30px;
      flex-wrap: wrap;
    }
    
    .stat-card {
      background: var(--card-bg);
      border-radius: 10px;
      padding: 20px;
      flex: 1 1 200px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }
    
    .stat-card .icon {
      font-size: 2rem;
      color: var(--secondary);
      margin-bottom: 10px;
    }
    
    .stat-card .count {
      font-size: 2rem;
      font-weight: 600;
      color: var(--primary);
      margin-bottom: 5px;
    }
    
    .stat-card .label {
      font-size: 0.9rem;
      color: var(--gray);
    }
    
    .search-container {
      position: relative;
      margin-bottom: 30px;
    }
    
    .search-container input[type="text"] {
      width: 100%;
      padding: 15px 20px;
      font-size: 1rem;
      border: none;
      border-radius: 50px;
      background-color: white;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
    }
    
    .search-container input[type="text"]:focus {
      outline: none;
      box-shadow: 0 0 0 3px rgba(60, 130, 246, 0.3);
    }
    
    .search-container .search-icon {
      position: absolute;
      right: 20px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--gray);
    }
    
    .patients-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
    }
    
    .patient-card {
      background-color: var(--card-bg);
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      position: relative;
    }
    
    .patient-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }
    
    .patient-header {
      background: linear-gradient(to right, var(--secondary), var(--primary));
      color: white;
      padding: 15px;
      position: relative;
    }
    
    .patient-code {
      font-size: 0.8rem;
      opacity: 0.8;
      margin-bottom: 5px;
    }
    
    .patient-name {
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 5px;
    }
    
    .patient-body {
      padding: 20px;
    }
    
    .patient-info {
      margin-bottom: 15px;
    }
    
    .info-item {
      display: flex;
      align-items: flex-start;
      margin-bottom: 10px;
    }
    
    .info-icon {
      color: var(--secondary);
      margin-right: 10px;
      font-size: 1rem;
      width: 20px;
    }
    
    .info-text {
      font-size: 0.95rem;
      color: var(--dark);
      word-break: break-word;
    }
    
    .email {
      font-size: 0.9rem;
      color: var(--gray);
    }
    
    .waiting-time {
      background-color: var(--light);
      padding: 15px;
      border-radius: 8px;
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    
    .waiting-label {
      font-size: 0.85rem;
      color: var(--gray);
      margin-bottom: 5px;
    }
    
    .waiting-days {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary);
    }
    
    .days-text {
      font-size: 1rem;
      color: var(--gray);
    }
    
    .blood-type {
      position: absolute;
      top: 15px;
      right: 15px;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: white;
      color: var(--danger);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 1rem;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    
    .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 500;
      margin-top: 10px;
    }
    
    .badge-dialysis {
      background-color: rgba(239, 68, 68, 0.1);
      color: var(--danger);
    }
    
    .badge-location {
      background-color: rgba(16, 185, 129, 0.1);
      color: var(--success);
    }
    
    .no-patients {
      text-align: center;
      padding: 50px 0;
      color: var(--gray);
      font-size: 1.2rem;
    }
    
    .no-patients i {
      font-size: 3rem;
      margin-bottom: 20px;
      color: var(--primary);
    }
    
    @media (max-width: 768px) {
      .header-content {
        flex-direction: column;
        text-align: center;
      }
      
      .hospital-info {
        margin-bottom: 15px;
      }
      
      .dashboard-stats {
        gap: 15px;
      }
      
      .stat-card {
        flex: 1 1 calc(50% - 15px);
      }
      
      .patients-grid {
        grid-template-columns: 1fr;
      }
    }
    
    @media (max-width: 480px) {
      .stat-card {
        flex: 1 1 100%;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="container">
      <div class="header-content">
        <div class="hospital-info">
          <h1>Patient Dashboard</h1>
          <p><?php echo htmlspecialchars($hospital_name); ?></p>
        </div>
        <div class="date-display">
          <?php echo date('l, F j, Y'); ?>
        </div>
      </div>
    </div>
  </header>
  
  <div class="container">
    <!-- Dashboard Statistics -->
    <div class="dashboard-stats">
      <div class="stat-card">
        <div class="icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="count" id="totalPatients"><?php echo count($patients); ?></div>
        <div class="label">Total Patients</div>
      </div>
      
      <div class="stat-card">
        <div class="icon">
          <i class="fas fa-procedures"></i>
        </div>
        <div class="count" id="onDialysis">0</div>
        <div class="label">On Dialysis</div>
      </div>
      
      <div class="stat-card">
        <div class="icon">
          <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="count" id="avgWaitingDays">0</div>
        <div class="label">Average Days Waiting</div>
      </div>
      
      <div class="stat-card">
        <div class="icon">
          <i class="fas fa-clock"></i>
        </div>
        <div class="count" id="longestWait">0</div>
        <div class="label">Longest Wait (Days)</div>
      </div>
    </div>
    
    <!-- Search Box -->
    <div class="search-container">
      <input type="text" id="searchInput" placeholder="Search by name, email, location, or patient code...">
      <span class="search-icon">
        <i class="fas fa-search"></i>
      </span>
    </div>
    
    <!-- Patients Grid -->
    <div class="patients-grid" id="patientsGrid">
      <?php if (count($patients) > 0): ?>
        <?php foreach($patients as $patient): ?>
          <div class="patient-card" data-created="<?php echo strtotime($patient['created_at']) * 1000; ?>">
            <div class="patient-header">
              <div class="patient-code"><?php echo htmlspecialchars($patient['patient_code']); ?></div>
              <div class="patient-name"><?php echo htmlspecialchars($patient['name']); ?></div>
              <?php if ($patient['blood_type']): ?>
                <div class="blood-type"><?php echo htmlspecialchars($patient['blood_type']); ?></div>
              <?php endif; ?>
            </div>
            <div class="patient-body">
              <div class="patient-info">
                <div class="info-item">
                  <div class="info-icon"><i class="fas fa-envelope"></i></div>
                  <div class="info-text email"><?php echo htmlspecialchars($patient['email']); ?></div>
                </div>
                <?php if (!empty($patient['location'])): ?>
                <div class="info-item">
                  <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                  <div class="info-text">
                    <?php echo htmlspecialchars($patient['location'], ENT_QUOTES, 'UTF-8'); ?>
                    <span class="badge badge-location">Location</span>
                  </div>
                </div>
                <?php endif; ?>
                <?php if ($patient['on_dialysis']): ?>
                <div class="info-item">
                  <div class="info-icon"><i class="fas fa-heartbeat"></i></div>
                  <div class="info-text">
                    Currently on dialysis
                    <span class="badge badge-dialysis">Dialysis</span>
                  </div>
                </div>
                <?php endif; ?>
              </div>
              <div class="waiting-time">
                <div class="waiting-label">Waiting Time</div>
                <div class="waiting-days waiting-days-count">0</div>
                <div class="days-text">days</div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="no-patients">
          <i class="fas fa-user-plus"></i>
          <p>No patients have been added yet.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script>
    // Conversion: 10 minutes in milliseconds equals 1 day (600,000 ms) for testing
    const conversionFactor = 600000;
    // For actual production use: const conversionFactor = 86400000; // 1 day in ms
    
    // Function to update waiting days dynamically for each patient card
    function updateWaitingDays() {
      const patientCards = document.querySelectorAll('.patient-card');
      let totalDays = 0;
      let longestWait = 0;
      let onDialysisCount = <?php echo array_reduce($patients, function($carry, $item) { return $carry + ($item['on_dialysis'] ? 1 : 0); }, 0); ?>;
      
      patientCards.forEach(card => {
        const createdTimestamp = parseInt(card.getAttribute('data-created'));
        const now = Date.now();
        const waitingDays = Math.floor((now - createdTimestamp) / conversionFactor);
        
        card.querySelector('.waiting-days-count').textContent = waitingDays;
        
        // Update statistics
        totalDays += waitingDays;
        if (waitingDays > longestWait) {
          longestWait = waitingDays;
        }
      });
      
      // Update dashboard stats
      document.getElementById('onDialysis').textContent = onDialysisCount;
      document.getElementById('longestWait').textContent = longestWait;
      
      const averageWait = patientCards.length > 0 ? Math.round(totalDays / patientCards.length) : 0;
      document.getElementById('avgWaitingDays').textContent = averageWait;
    }
    
    // Basic search/filter functionality
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('keyup', function() {
      const filter = searchInput.value.toLowerCase();
      const cards = document.querySelectorAll('.patient-card');
      
      cards.forEach(card => {
        const name = card.querySelector('.patient-name').textContent.toLowerCase();
        const code = card.querySelector('.patient-code').textContent.toLowerCase();
        const email = card.querySelector('.email').textContent.toLowerCase();
        const locationEl = card.querySelector('.info-text:not(.email)');
        const location = locationEl ? locationEl.textContent.toLowerCase() : '';
        
        if (name.includes(filter) || code.includes(filter) || email.includes(filter) || location.includes(filter)) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });
    
    // Update waiting days every second
    setInterval(updateWaitingDays, 1000);
    updateWaitingDays();
    
    // Animation for stats numbers
    document.addEventListener('DOMContentLoaded', function() {
      const statCounts = document.querySelectorAll('.stat-card .count');
      statCounts.forEach(stat => {
        const value = parseInt(stat.textContent);
        stat.textContent = '0';
        
        let startValue = 0;
        const duration = 1000;
        const startTime = performance.now();
        
        function animateValue(timestamp) {
          const runTime = timestamp - startTime;
          const progress = Math.min(runTime / duration, 1);
          
          const currentValue = Math.floor(progress * value);
          stat.textContent = currentValue;
          
          if (runTime < duration) {
            requestAnimationFrame(animateValue);
          } else {
            stat.textContent = value;
          }
        }
        
        requestAnimationFrame(animateValue);
      });
    });
  </script>
</body>
</html>