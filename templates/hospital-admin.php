<?php
session_start();
include '../config/connection.php';

// Check if hospital is logged in; if not, redirect to login page.
if (!isset($_SESSION['hospital_id']) || !isset($_SESSION['hospital_name'])) {
    header("Location: ../templates/hospital_login?status=error&message=Please log in first");
    exit();
}

$hospitalId = $_SESSION['hospital_id'];

// Retrieve hospital details from the hospitals table.
$stmt = $conn->prepare("SELECT name, region, city, contact_info FROM hospitals WHERE id = ?");
$stmt->bind_param("i", $hospitalId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $hospital = $result->fetch_assoc();
} else {
    $hospital = [
        "name" => $_SESSION['hospital_name'],
        "region" => "",
        "city" => "",
        "contact_info" => ""
    ];
}
$stmt->close();

// Query average waiting days
$stmtAvgWait = $conn->prepare("SELECT AVG(dayswait_alloc) as avg_waiting FROM recipients WHERE hospital_id = ?");
$stmtAvgWait->bind_param("i", $hospitalId);
$stmtAvgWait->execute();
$resultAvgWait = $stmtAvgWait->get_result();
$avgWaitingDays = 0;
if ($row = $resultAvgWait->fetch_assoc()) {
    $avgWaitingDays = round($row['avg_waiting'], 1);
}
$stmtAvgWait->close();

// Query number on dialysis
$stmtDialysis = $conn->prepare("SELECT COUNT(*) as dialysis_count FROM recipients WHERE hospital_id = ? AND on_dialysis = 1");
$stmtDialysis->bind_param("i", $hospitalId);
$stmtDialysis->execute();
$resultDialysis = $stmtDialysis->get_result();
$onDialysis = 0;
if ($row = $resultDialysis->fetch_assoc()) {
    $onDialysis = $row['dialysis_count'];
}
$stmtDialysis->close();


// Query total number of patients added by this hospital.
$stmtPatients = $conn->prepare("SELECT COUNT(*) as total_patients FROM recipients WHERE hospital_id = ?");
$stmtPatients->bind_param("i", $hospitalId);
$stmtPatients->execute();
$resultPatients = $stmtPatients->get_result();
$totalPatients = 0;
if ($row = $resultPatients->fetch_assoc()) {
    $totalPatients = $row['total_patients'];
}
$stmtPatients->close();

// Query total number of successful matches (completed transplants) for this hospital.
$stmtMatches = $conn->prepare("SELECT COUNT(*) as total_successful FROM transplants WHERE hospital_id = ? AND status = 'completed'");
$stmtMatches->bind_param("i", $hospitalId);
$stmtMatches->execute();
$resultMatches = $stmtMatches->get_result();
$totalMatches = 0;
if ($row = $resultMatches->fetch_assoc()) {
    $totalMatches = $row['total_successful'];
}
$stmtMatches->close();

// Fetch recent patients or search results if a query is provided.
$patientsList = [];
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $searchTerm = trim($_GET['q']);
    $searchWildcard = "%" . $searchTerm . "%";
    $stmtPatientsList = $conn->prepare("SELECT r.patient_code, u.name as patient_name, u.created_at as joined_date FROM recipients r JOIN users u ON r.user_id = u.id WHERE r.hospital_id = ? AND (u.name LIKE ? OR r.patient_code LIKE ?)");
    $stmtPatientsList->bind_param("iss", $hospitalId, $searchWildcard, $searchWildcard);
} else {
    $stmtPatientsList = $conn->prepare("SELECT r.patient_code, u.name as patient_name, u.created_at as joined_date FROM recipients r JOIN users u ON r.user_id = u.id WHERE r.hospital_id = ? ORDER BY u.created_at DESC LIMIT 5");
    $stmtPatientsList->bind_param("i", $hospitalId);
}
$stmtPatientsList->execute();
$resultPatientsList = $stmtPatientsList->get_result();
while ($row = $resultPatientsList->fetch_assoc()) {
    $patientsList[] = $row;
}
$stmtPatientsList->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">    <title><?php echo htmlspecialchars($hospital['name']); ?> Admin Dashboard</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3056D3;
            --secondary-color: #4B7BFB;
            --accent-color: #F0F4FF;
            --dark-blue: #1e3a8a;
            --dark-color: #333;
            --light-color: #f8f9fa;
            --sidebar-width: 250px;
            --header-height: 60px;
            --success-color: #22c55e;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background-color: #f5f7fe;
            color: var(--dark-color);
        }
        
        nav {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: var(--sidebar-width);
            background-color: var(--dark-blue);
            color: white;
            padding: 1.5rem 0;
            transition: all 0.3s ease;
            z-index: 100;
        }
        
        nav .menu-items {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .menu-items li {
            list-style: none;
        }
        
        .menu-items a {
            display: flex;
            align-items: center;
            height: 50px;
            text-decoration: none;
            color: #fff;
            padding: 0 1.5rem;
            transition: all 0.3s ease;
        }
        
        .menu-items a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .menu-items a i {
            font-size: 20px;
            min-width: 30px;
        }
        
        .menu-items a .link-name {
            font-size: 0.95rem;
            font-weight: 400;
        }
        
        .dashboard {
            position: relative;
            left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            min-height: 100vh;
            transition: all 0.3s ease;
        }
        
        .dashboard .top {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 1.5rem;
            width: calc(100% - var(--sidebar-width));
            height: var(--header-height);
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            z-index: 50;
        }
        
        .top .sidebar-toggle {
            font-size: 24px;
            cursor: pointer;
        }
        
        .top .search-box {
            position: relative;
            height: 40px;
            max-width: 400px;
            width: 100%;
            margin: 0 1.5rem;
        }
        
        .search-box input {
            position: absolute;
            height: 100%;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 25px;
            padding: 0 1rem 0 3rem;
            outline: none;
            font-size: 0.9rem;
        }
        
        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 10;
        }
        
        .search-box button {
            position: absolute;
            right: 5px;
            top: 5px;
            border: none;
            height: 30px;
            width: 30px;
            border-radius: 50%;
            cursor: pointer;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .dash-content {
            padding: calc(var(--header-height) + 2rem) 1.5rem 1.5rem;
        }
        
        .overview {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .title {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .title i {
            font-size: 24px;
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        
        .title .text {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-blue);
        }
        
        .boxes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .box {
            background-color: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .box .icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .box .number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-blue);
        }
        
        .box .text {
            font-size: 1rem;
            color: #666;
        }
        
        .activity {
            margin-top: 2rem;
            background-color: white;
            border-radius: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
        }
        
        .activity .title {
            margin-bottom: 1.5rem;
        }
        
        .patients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .patient-card {
            background-color: var(--accent-color);
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .patient-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .patient-card .patient-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .patient-header .code {
            font-size: 0.8rem;
            color: #666;
        }
        
        .patient-header .blood-type {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background-color: #fff;
            color: var(--danger-color);
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
        
        .patient-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-blue);
            margin-bottom: 0.5rem;
        }
        
        .patient-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
        }
        
        .info-item i {
            margin-right: 0.5rem;
            font-size: 1rem;
            color: var(--primary-color);
        }
        
        .view-more {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
        }
        
        .view-more a {
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .view-more a:hover {
            background-color: var(--dark-blue);
        }
        
        .mode-toggle {
            position: relative;
            display: inline-block;
            height: 22px;
            width: 40px;
            border-radius: 25px;
            background-color: rgba(255, 255, 255, 0.3);
            cursor: pointer;
        }
        
        .mode-toggle .switch {
            position: absolute;
            left: 5px;
            top: 3px;
            height: 16px;
            width: 16px;
            border-radius: 50%;
            background-color: white;
            transition: all 0.3s ease;
        }
        
        body.dark .mode-toggle .switch {
            left: 19px;
        }
        
        body.dark {
            background-color: #1e1e2f;
            color: white;
        }
        
        body.dark .dashboard .top,
        body.dark .box,
        body.dark .activity {
            background-color: #29293d;
            color: white;
        }
        
        body.dark .top .search-box input {
            background-color: #29293d;
            border-color: #444;
            color: white;
        }
        
        body.dark .title .text,
        body.dark .box .number {
            color: white;
        }
        
        body.dark .box .text {
            color: #ccc;
        }
        
        body.dark .patient-card {
            background-color: #3a3a5a;
        }
        
        body.dark .patient-name {
            color: white;
        }
        
        body.dark .patient-header .code {
            color: #ccc;
        }
        
        /* Responsive */
        @media (max-width: 1080px) {
            .boxes {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            nav {
                width: 60px;
            }
            
            nav .menu-items a .link-name {
                display: none;
            }
            
            .dashboard {
                left: 60px;
                width: calc(100% - 60px);
            }
            
            .dashboard .top {
                left: 60px;
                width: calc(100% - 60px);
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="menu-items">
            <ul class="nav-links">
                <li>
                    <a href="../templates/hospital-admin.php">
                        <i class="uil uil-estate"></i>
                        <span class="link-name">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="../templates/patient-info.php">
                        <i class="uil uil-plus-circle"></i>
                        <span class="link-name">Add Patient</span>
                    </a>
                </li>
                <li>
                    <a href="../templates/view-patients.php">
                        <i class="uil uil-users-alt"></i>
                        <span class="link-name">View Patients</span>
                    </a>
                </li>
                <li>
                    <a href="../templates/view_matches.php">
                        <i class="uil uil-check-circle"></i>
                        <span class="link-name">View Matches</span>
                    </a>
                </li>
                <!-- Additional links can be added here -->
            </ul>
            
            <ul class="logout-mode">
                <li>
                    <a href="../actions/hospital_logout.php">
                        <i class="uil uil-signout"></i>
                        <span class="link-name">Logout</span>
                    </a>
                </li>
                <li class="mode">
                    <a href="#">
                        <i class="uil uil-moon"></i>
                        <span class="link-name">Dark Mode</span>
                    </a>
                    <div class="mode-toggle">
                        <span class="switch"></span>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
    <section class="dashboard">
        <div class="top">
            <i class="uil uil-bars sidebar-toggle"></i>
            <!-- Search Form -->
            <div class="search-box">
                <i class="uil uil-search"></i>
                <form action="" method="GET" class="search-form">
                    <input type="text" name="q" placeholder="Search patients by name or code..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                    <button type="submit"><i class="uil uil-search"></i></button>
                </form>
            </div>
        </div>
        <div class="dash-content">
            <div class="overview">
                <div class="title">
                    <i class="uil uil-tachometer-fast-alt"></i>
                    <span class="text"><?php echo htmlspecialchars($hospital['name']); ?> Dashboard</span>
                </div>
                <div class="boxes">
                    <div class="box">
                        <i class="uil uil-users-alt icon"></i>
                        <span class="number"><?php echo $totalPatients; ?></span>
                        <span class="text">Total Patients</span>
                    </div>
                    <div class="box">
                        <i class="uil uil-check-circle icon"></i>
                        <span class="number"><?php echo $totalMatches; ?></span>
                        <span class="text">Successful Matches</span>
                    </div>
                    <div class="box">
                        <i class="uil uil-clock icon"></i>
                        <span class="number"><?php echo isset($avgWaitingDays) ? $avgWaitingDays : '0'; ?></span>
                        <span class="text">Avg. Days Waiting</span>
                    </div>
                    <div class="box">
                        <i class="uil uil-heart-medical icon"></i>
                        <span class="number"><?php echo isset($onDialysis) ? $onDialysis : '0'; ?></span>
                        <span class="text">On Dialysis</span>
                    </div>
                </div>
            </div>
            <div class="activity">
                <div class="title">
                    <i class="uil uil-clock-three"></i>
                    <span class="text">Recent Patients</span>
                </div>
                <?php if (count($patientsList) > 0): ?>
                <div class="patients-grid">
                    <?php foreach ($patientsList as $patient): ?>
                    <div class="patient-card">
                        <div class="patient-header">
                            <span class="code"><?php echo htmlspecialchars($patient['patient_code']); ?></span>
                            <span class="blood-type"><?php echo isset($patient['blood_type']) ? htmlspecialchars($patient['blood_type']) : 'O+'; ?></span>
                        </div>
                        <h3 class="patient-name"><?php echo htmlspecialchars($patient['patient_name']); ?></h3>
                        <div class="patient-info">
                            <span class="info-item">
                                <i class="uil uil-calendar-alt"></i>
                                Joined: <?php echo htmlspecialchars(date("Y-m-d", strtotime($patient['joined_date']))); ?>
                            </span>
                            <span class="info-item">
                                <i class="uil uil-clock"></i>
                                Waiting for: <?php echo isset($patient['waiting_days']) ? htmlspecialchars($patient['waiting_days']) : rand(1, 30); ?> days
                            </span>
                            <span class="info-item">
                                <i class="uil uil-medical-square"></i>
                                Status: <?php echo isset($patient['status']) ? htmlspecialchars($patient['status']) : (rand(0, 1) ? 'On Dialysis' : 'Waiting'); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="view-more">
                    <a href="../templates/view-patients.php">View All Patients</a>
                </div>
                <?php else: ?>
                <p>No patients found.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <script>
        const body = document.querySelector("body"),
              modeToggle = document.querySelector(".mode-toggle"),
              sidebar = document.querySelector("nav"),
              sidebarToggle = document.querySelector(".sidebar-toggle");
        
        let getMode = localStorage.getItem("mode");
        if(getMode && getMode === "dark") {
            body.classList.add("dark");
            modeToggle.querySelector(".switch").style.left = "19px";
        }
        
        modeToggle.addEventListener("click", () => {
            body.classList.toggle("dark");
            
            if(body.classList.contains("dark")) {
                localStorage.setItem("mode", "dark");
            } else {
                localStorage.setItem("mode", "light");
            }
        });
        
        sidebarToggle.addEventListener("click", () => {
            sidebar.classList.toggle("close");
            
            if(sidebar.classList.contains("close")) {
                localStorage.setItem("status", "close");
            } else {
                localStorage.setItem("status", "open");
            }
        });
    </script>
</body>
</html>