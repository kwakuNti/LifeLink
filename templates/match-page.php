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
    <title>LifeLink - Donor Match Finder</title>
    <style>
        :root {
            --primary: #1a73e8;
            --primary-hover: #1557b0;
            --primary-light: #e8f0fe;
            --success: #34a853;
            --warning: #fbbc05;
            --gray-100: #f8f9fa;
            --gray-200: #f4f4f4;
            --gray-300: #e6e6e6;
            --gray-500: #666;
            --gray-900: #333;
            --white: #ffffff;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 8px rgba(0, 0, 0, 0.1);
            --radius-sm: 8px;
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
        }
        h2 {
            font-size: 1.25rem;
            margin-bottom: var(--spacing-md);
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

        /* Header */
        .navbar {
            padding: var(--spacing-md);
        }

        .navbar-content {
            display: flex;
            align-items: center;
            justify-content: center; /* center the contents */
        }

        .logo img {
            height: 70px;
            width: auto;
        }

        .navbar .container {
            text-align: center;
        }

        /* Main Layout */
        .main-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--spacing-md);
            padding: var(--spacing-md) var(--spacing-sm);
        }

        .card {
            border-radius: var(--radius-sm);
            padding: var(--spacing-md);
            transition: var(--transition-base);
            height: 100%;
        }

        .card:hover {
            box-shadow: var(--shadow-md);
        }

        /* Stats */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--spacing-sm);
            margin: var(--spacing-md) 0;
        }

        .stat-box {
            background: var(--primary-light);
            padding: var(--spacing-sm);
            border-radius: var(--radius-sm);
            text-align: center;
            transition: var(--transition-base);
        }
        .stat-box:hover {
            transform: translateY(-2px);
        }
        .stat-box h3 {
            color: var(--primary);
            font-size: 1.5rem;
            font-weight: 700;
        }

        /* Matching Section */
        .match-panel {
            text-align: center;
        }
        .match-button {
            width: 100%;
            max-width: 320px;
            height: 56px;
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: var(--radius-sm);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
            transition: var(--transition-base);
            margin: var(--spacing-lg) auto;
            position: relative;
            overflow: hidden;
        }
        .match-button:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }
        .match-button:active {
            transform: translateY(0);
        }
        .match-button::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: var(--radius-full);
            transform: translate(-50%, -50%) scale(1);
            transition: width 0.6s ease-out, height 0.6s ease-out, opacity 0.6s ease-out;
        }
        .match-button:hover::after {
            width: 300px;
            height: 300px;
            opacity: 0.2;
        }

        .matching-container {
            display: none;
            margin: 0 auto;
            max-width: 500px;
        }

        /* Progress Circle */
        .progress-container {
            position: relative;
            margin: var(--spacing-lg) auto;
        }

        .progress-circle {
            position: relative;
            width: 180px;
            height: 180px;
            margin: 0 auto;
        }
        .progress-circle svg {
            transform: rotate(-90deg);
        }
        .progress-circle-bg {
            fill: none;
            stroke: var(--gray-300);
            stroke-width: 8;
        }
        .progress-circle-path {
            fill: none;
            stroke: var(--primary);
            stroke-width: 8;
            stroke-dasharray: 0 377;
            transition: stroke-dasharray 0.7s ease;
            stroke-linecap: round;
        }
        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.75rem;
            font-weight: bold;
            color: var(--primary);
        }

        /* Match Results */
        .name-display {
            font-size: 1.5rem;
            color: var(--primary);
            font-weight: 600;
            height: 40px;
            margin: var(--spacing-md) 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .match-quality {
            font-size: 1.25rem;
            color: var(--success);
            font-weight: 600;
            margin: var(--spacing-md) 0;
            display: none;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        .match-quality.show {
            opacity: 1;
            transform: translateY(0);
        }

        .match-details {
            background: var(--gray-100);
            border-radius: var(--radius-sm);
            padding: var(--spacing-md);
            margin: var(--spacing-md) 0;
            text-align: left;
            display: none;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        .match-details.show {
            opacity: 1;
            transform: translateY(0);
        }

        .match-details h3 {
            color: var(--primary);
            margin-bottom: var(--spacing-sm);
        }
        .match-details ul {
            list-style: none;
        }
        .match-details li {
            margin-bottom: var(--spacing-xs);
            padding-left: var(--spacing-md);
            position: relative;
            display: flex;
            align-items: center;
        }
        .match-details li::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: var(--radius-full);
            background-color: var(--primary);
            position: absolute;
            left: 5px;
            top: 10px;
        }

        /* Updated Criteria List to reflect your model features */
        .criteria-list {
            margin: var(--spacing-md) 0;
        }
        .criteria-list li {
            margin-bottom: var(--spacing-xs);
            padding-left: var(--spacing-md);
            position: relative;
            display: flex;
            align-items: center;
        }
        .criteria-list li::before {
            content: "✓";
            color: var(--success);
            position: absolute;
            left: 0;
        }

        /* Status Indicators */
        .status-list {
            margin-top: var(--spacing-sm);
        }
        .status-item {
            display: flex;
            align-items: center;
            margin-bottom: var(--spacing-xs);
            padding: var(--spacing-xs);
            border-radius: var(--radius-sm);
            transition: var(--transition-base);
        }
        .status-item:hover {
            background-color: var(--gray-100);
        }
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: var(--radius-full);
            margin-right: var(--spacing-xs);
            flex-shrink: 0;
        }
        .status-active {
            background-color: var(--success);
        }
        .status-pending {
            background-color: var(--warning);
        }
        .timestamp {
            color: var(--gray-500);
            font-size: 0.85rem;
            margin-left: auto;
        }

        /* Buttons */
        .reset-button {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: var(--radius-sm);
            padding: 12px 24px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            display: none;
            opacity: 0;
            transform: translateY(10px);
            transition: background-color 0.3s ease, transform 0.5s ease, opacity 0.5s ease;
            margin: 0 auto;
        }
        .reset-button.show {
            opacity: 1;
            transform: translateY(0);
        }
        .reset-button:hover {
            background-color: var(--primary-hover);
        }

        /* Animations */
        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(0.98); }
            100% { opacity: 1; transform: scale(1); }
        }
        .pulse {
            animation: pulse 1.2s infinite;
        }

        /* Responsive Design */
        @media (min-width: 768px) {
            .main-container {
                grid-template-columns: 1fr 2fr;
                padding: var(--spacing-lg);
            }
            h1 {
                font-size: 2rem;
            }
            .match-button {
                max-width: 360px;
                height: 60px;
                font-size: 1.1rem;
            }
        }

        @media (min-width: 1200px) {
            .main-container {
                grid-template-columns: 1fr 2fr 1fr;
            }
            .card {
                padding: var(--spacing-lg);
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container navbar-content">
            <div class="logo">
                <img src="../assets/images/logo-removebg-preview.png" alt="LifeLink Logo">
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="main-container">
            <!-- Match Criteria Card (Content Updated) -->
            <div class="card">
                <h2>Match Criteria</h2>
                <ul class="criteria-list">
                    <li>Age (INIT_AGE)</li>
                    <li>BMI (BMI_TCR)</li>
                    <li>Days on Waiting List (DAYSWAIT_ALLOC)</li>
                    <li>Kidney Cluster</li>
                    <li>Diagnosis Code (DGN_TCR)</li>
                    <li>Weight (WGT_KG_TCR)</li>
                    <li>Height (HGT_CM_TCR)</li>
                    <li>Glomerular Filtration Rate (GFR)</li>
                    <li>On Dialysis (Y/N)</li>
                    <li>ABO Blood Type</li>
                </ul>

                <div class="stats-container">
                    <div class="stat-box">
                        <h3>3,500+</h3>
                        <p>Active Recipients</p>
                    </div>
                    <div class="stat-box">
                        <h3>85%</h3>
                        <p>Success Rate</p>
                    </div>
                </div>
            </div>

            <!-- Organ Donor Match Finder (unchanged design) -->
            <div class="card match-panel">
                <h1>Organ Donor Match Finder</h1>
                <p style="margin: var(--spacing-md) 0; color: var(--gray-500);">
                    Finding the right match saves lives. Our algorithm considers multiple medical and logistical factors 
                    to ensure the best possible outcome.
                </p>

                <button class="match-button" id="matchButton">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" 
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" 
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2a8 8 0 0 0-8 8c0 5.4 7 11.5 7.3 11.8a1 1 0 0 0 1.3 0C13 21.5 20 15.4 20 10a8 8 0 0 0-8-8z"/>
                    </svg>
                    Find Compatible Match
                </button>

                <div class="matching-container" id="matchingContainer">
                    <div class="name-display" id="nameDisplay"></div>
                    
                    <div class="progress-container">
                        <div class="progress-circle">
                            <svg width="180" height="180">
                                <circle class="progress-circle-bg" cx="90" cy="90" r="60"/>
                                <circle class="progress-circle-path" cx="90" cy="90" r="60"/>
                            </svg>
                            <div class="progress-text" id="progressText">0%</div>
                        </div>
                    </div>
                    
                    <div class="match-quality" id="matchQuality"></div>

                    <div class="match-details" id="matchDetails">
                        <h3>Match Details</h3>
                        <ul>
                            <li>Blood Type: A+ (Compatible)</li>
                            <li>HLA Match: 5/6 antigens</li>
                            <li>Size Match: 98% compatible</li>
                            <li>Distance: 45 miles</li>
                            <li>Estimated Transport Time: 35 minutes</li>
                        </ul>
                    </div>

                    <button class="reset-button" id="resetButton">Find Another Match</button>
                </div>
            </div>

            <!-- Active Matches & Recent Successful Matches (unchanged design) -->
            <div class="card">
                <h2>Active Matches</h2>
                <div class="status-list">
                    <div class="status-item">
                        <span class="status-indicator status-active"></span>
                        <span>John D. → Sarah M.</span>
                        <span class="timestamp">In Progress</span>
                    </div>
                    <div class="status-item">
                        <span class="status-indicator status-pending"></span>
                        <span>Michael R. → David K.</span>
                        <span class="timestamp">Pending</span>
                    </div>
                    <div class="status-item">
                        <span class="status-indicator status-active"></span>
                        <span>Lisa T. → James W.</span>
                        <span class="timestamp">In Progress</span>
                    </div>
                </div>

                <h2 style="margin-top: var(--spacing-xl);">Recent Successful Matches</h2>
                <div class="status-list">
                    <div class="status-item">
                        <span>Emily C. → Robert M.</span>
                        <span class="timestamp">2 days ago</span>
                    </div>
                    <div class="status-item">
                        <span>Alex P. → Maria S.</span>
                        <span class="timestamp">5 days ago</span>
                    </div>
                    <div class="status-item">
                        <span>Thomas H. → Karen L.</span>
                        <span class="timestamp">1 week ago</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Patient data
        const patients = [
            {
                id: 'Patient #45892',
                bloodType: 'O+',
                hlaMatch: '4/6 antigens',
                sizeMatch: '92% compatible',
                distance: '65 miles',
                transportTime: '50 minutes',
                matchScore: 78
            },
            {
                id: 'Patient #67234',
                bloodType: 'A+',
                hlaMatch: '5/6 antigens',
                sizeMatch: '98% compatible',
                distance: '45 miles',
                transportTime: '35 minutes',
                matchScore: 91
            },
            {
                id: 'Patient #89123',
                bloodType: 'B-',
                hlaMatch: '4/6 antigens',
                sizeMatch: '85% compatible',
                distance: '120 miles',
                transportTime: '90 minutes',
                matchScore: 68
            },
            {
                id: 'Patient #34567',
                bloodType: 'AB+',
                hlaMatch: '6/6 antigens',
                sizeMatch: '94% compatible',
                distance: '30 miles',
                transportTime: '25 minutes',
                matchScore: 93
            },
            {
                id: 'Patient #78901',
                bloodType: 'O-',
                hlaMatch: '3/6 antigens',
                sizeMatch: '80% compatible',
                distance: '85 miles',
                transportTime: '65 minutes',
                matchScore: 63
            }
        ];

        // DOM Elements
        const matchButton = document.getElementById('matchButton');
        const matchingContainer = document.getElementById('matchingContainer');
        const nameDisplay = document.getElementById('nameDisplay');
        const progressCircle = document.querySelector('.progress-circle-path');
        const progressText = document.getElementById('progressText');
        const matchQuality = document.getElementById('matchQuality');
        const resetButton = document.getElementById('resetButton');
        const matchDetails = document.getElementById('matchDetails');

        // Helper Functions
        function getMatchQuality(percentage) {
            if (percentage >= 90) return 'Excellent Match (Type A)';
            if (percentage >= 75) return 'Strong Match (Type B)';
            if (percentage >= 60) return 'Potential Match (Type C)';
            return 'Limited Match (Type D)';
        }

        function updateMatchDetails(patient) {
            const detailsList = matchDetails.querySelector('ul');
            detailsList.innerHTML = `
                <li>Blood Type: ${patient.bloodType} (Compatible)</li>
                <li>HLA Match: ${patient.hlaMatch}</li>
                <li>Size Match: ${patient.sizeMatch}</li>
                <li>Distance: ${patient.distance}</li>
                <li>Estimated Transport Time: ${patient.transportTime}</li>
            `;
        }

        function animateProgress(targetPercentage, patient) {
            let current = 0;
            const interval = setInterval(() => {
                if (current >= targetPercentage) {
                    clearInterval(interval);
                    
                    // Update match quality and show with animation
                    matchQuality.textContent = getMatchQuality(targetPercentage);
                    matchQuality.style.display = 'block';
                    setTimeout(() => matchQuality.classList.add('show'), 100);
                    
                    // Update and show match details with animation
                    updateMatchDetails(patient);
                    matchDetails.style.display = 'block';
                    setTimeout(() => matchDetails.classList.add('show'), 300);
                    
                    // Show reset button with animation
                    resetButton.style.display = 'block';
                    setTimeout(() => resetButton.classList.add('show'), 500);
                    
                    // Remove pulse animation
                    nameDisplay.classList.remove('pulse');
                } else {
                    current += 1;
                    progressCircle.style.strokeDasharray = `${current * 3.77} 377`;
                    progressText.textContent = `${current}%`;
                }
            }, 20);
        }

        function resetMatchingUI() {
            // Reset all animations and displays
            matchQuality.classList.remove('show');
            matchDetails.classList.remove('show');
            resetButton.classList.remove('show');
            
            setTimeout(() => {
                progressCircle.style.strokeDasharray = '0 377';
                progressText.textContent = '0%';
                matchQuality.style.display = 'none';
                matchDetails.style.display = 'none';
                resetButton.style.display = 'none';
            }, 300);
        }

        function startMatching() {
            // Hide match button and show matching container
            matchButton.style.display = 'none';
            matchingContainer.style.display = 'block';
            
            // Reset UI if coming from a previous match
            resetMatchingUI();
            
            // Start name cycling animation
            nameDisplay.classList.add('pulse');
            
            let nameIndex = 0;
            const nameInterval = setInterval(() => {
                nameDisplay.textContent = patients[nameIndex % patients.length].id;
                nameIndex++;
            }, 150);

            // Select a random patient (weighted toward better matches)
            let finalPatient;
            const rand = Math.random();
            if (rand > 0.7) {
                // Higher chance of excellent match
                finalPatient = patients.find(p => p.matchScore >= 90);
            } else if (rand > 0.4) {
                // Medium chance of strong match
                finalPatient = patients.find(p => p.matchScore >= 75 && p.matchScore < 90);
            } else {
                // Lower chance of potential match
                finalPatient = patients.find(p => p.matchScore < 75);
            }

            // Stop name cycling and display final result after delay
            setTimeout(() => {
                clearInterval(nameInterval);
                nameDisplay.textContent = finalPatient.id;
                animateProgress(finalPatient.matchScore, finalPatient);
            }, 3000);
        }

        // Event Listeners
        matchButton.addEventListener('click', startMatching);
        
        resetButton.addEventListener('click', () => {
            resetMatchingUI();
            setTimeout(() => {
                startMatching();
            }, 500);
        });

        // Microinteractions
        const statBoxes = document.querySelectorAll('.stat-box');
        statBoxes.forEach(box => {
            box.addEventListener('mouseenter', () => {
                box.style.transform = 'translateY(-4px)';
            });
            box.addEventListener('mouseleave', () => {
                box.style.transform = 'translateY(0)';
            });
        });

        const statusItems = document.querySelectorAll('.status-item');
        statusItems.forEach(item => {
            item.addEventListener('mouseenter', () => {
                item.style.transform = 'translateX(5px)';
            });
            item.addEventListener('mouseleave', () => {
                item.style.transform = 'translateX(0)';
            });
        });
    </script>
</body>
</html>
