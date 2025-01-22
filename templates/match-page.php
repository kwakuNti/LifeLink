<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon_io/favicon-16x16.png">
    <link rel="manifest" href="../favicon_io/site.webmanifest">
    <title>LifeLink - Donor Match Finder</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto Mono', monospace;
        }

        body {
            min-height: 100vh;
            background-color: #f4f4f4;
        }

        .navbar {
            background-color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .main-container {
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            gap: 2rem;
            padding: 2rem;
            min-height: calc(100vh - 64px);
        }

        .left-panel, .right-panel {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .match-panel {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-box {
            background: #e8f0fe;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .stat-box h3 {
            color: #1a73e8;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .match-button {
            width: 100%;
            height: 64px;
            background-color: #1a73e8;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.2rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: background-color 0.3s;
            margin: 2rem 0;
        }

        .match-button:hover {
            background-color: #1557b0;
        }

        .matching-container {
            display: none;
        }

        .name-display {
            font-size: 1.8rem;
            color: #1a73e8;
            font-weight: bold;
            height: 40px;
            margin: 1.5rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .progress-circle {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 2rem auto;
        }

        .progress-circle svg {
            transform: rotate(-90deg);
        }

        .progress-circle-bg {
            fill: none;
            stroke: #e6e6e6;
            stroke-width: 8;
        }

        .progress-circle-path {
            fill: none;
            stroke: #1a73e8;
            stroke-width: 8;
            stroke-dasharray: 0 377;
            transition: stroke-dasharray 0.5s ease;
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2rem;
            font-weight: bold;
            color: #1a73e8;
        }

        .match-quality {
            font-size: 1.5rem;
            color: #34a853;
            font-weight: bold;
            margin: 1rem 0;
            display: none;
        }

        .match-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
            text-align: left;
            display: none;
        }

        .match-details h3 {
            color: #1a73e8;
            margin-bottom: 1rem;
        }

        .match-details ul {
            list-style: none;
        }

        .match-details li {
            margin-bottom: 0.5rem;
            padding-left: 1.5rem;
            position: relative;
        }

        .match-details li::before {
            content: "•";
            color: #1a73e8;
            position: absolute;
            left: 0;
        }

        .reset-button {
            background-color: #1a73e8;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 1rem;
            cursor: pointer;
            display: none;
        }

        .criteria-list {
            margin: 1rem 0;
        }

        .criteria-list li {
            margin-bottom: 0.8rem;
            padding-left: 1.5rem;
            position: relative;
        }

        .criteria-list li::before {
            content: "✓";
            color: #34a853;
            position: absolute;
            left: 0;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .pulse {
            animation: pulse 1s infinite;
        }

        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-active {
            background-color: #34a853;
        }

        .status-pending {
            background-color: #fbbc05;
        }

        @media (max-width: 1200px) {
            .main-container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1 style="color: #1a73e8;">LifeLink Match Center</h1>
    </nav>

    <div class="main-container">
        <div class="left-panel">
            <h2>Match Criteria</h2>
            <ul class="criteria-list">
                <li>Blood Type Compatibility</li>
                <li>HLA Matching</li>
                <li>Size Compatibility</li>
                <li>Medical Urgency</li>
                <li>Wait Time</li>
                <li>Geographic Distance</li>
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

        <div class="match-panel">
            <h1>Organ Donor Match Finder</h1>
            <p style="margin: 1rem 0; color: #666;">Finding the right match saves lives. Our algorithm considers multiple medical and logistical factors to ensure the best possible outcome.</p>

            <button class="match-button" id="matchButton">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12 2a8 8 0 0 0-8 8c0 5.4 7 11.5 7.3 11.8a1 1 0 0 0 1.3 0C13 21.5 20 15.4 20 10a8 8 0 0 0-8-8z"/>
                </svg>
                Find Compatible Match
            </button>

            <div class="matching-container" id="matchingContainer">
                <div class="name-display" id="nameDisplay"></div>
                <div class="progress-circle">
                    <svg width="200" height="200">
                        <circle class="progress-circle-bg" cx="100" cy="100" r="60"/>
                        <circle class="progress-circle-path" cx="100" cy="100" r="60"/>
                    </svg>
                    <div class="progress-text" id="progressText">0%</div>
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

        <div class="right-panel">
            <h2>Active Matches</h2>
            <div style="margin-top: 1rem;">
                <p style="margin-bottom: 0.8rem;">
                    <span class="status-indicator status-active"></span>
                    John D. → Sarah M. (In Progress)
                </p>
                <p style="margin-bottom: 0.8rem;">
                    <span class="status-indicator status-pending"></span>
                    Michael R. → David K. (Pending)
                </p>
                <p style="margin-bottom: 0.8rem;">
                    <span class="status-indicator status-active"></span>
                    Lisa T. → James W. (In Progress)
                </p>
                
            </div>

            <h2 style="margin-top: 2rem;">Recent Successful Matches</h2>
            <ul style="list-style: none; margin-top: 1rem;">
                <li style="margin-bottom: 0.8rem;">Emily C. → Robert M. (2 days ago)</li>
                <li style="margin-bottom: 0.8rem;">Alex P. → Maria S. (5 days ago)</li>
                <li style="margin-bottom: 0.8rem;">Thomas H. → Karen L. (1 week ago)</li>
            </ul>
            
        </div>
        
    </div>

    <script>
        const names = [
            'Patient #45892', 'Patient #67234', 'Patient #89123', 'Patient #34567',
            'Patient #78901', 'Patient #23456', 'Patient #90123', 'Patient #12345'
        ];

        const matchButton = document.getElementById('matchButton');
        const matchingContainer = document.getElementById('matchingContainer');
        const nameDisplay = document.getElementById('nameDisplay');
        const progressCircle = document.querySelector('.progress-circle-path');
        const progressText = document.getElementById('progressText');
        const matchQuality = document.getElementById('matchQuality');
        const resetButton = document.getElementById('resetButton');
        const matchDetails = document.getElementById('matchDetails');

        function getMatchQuality(percentage) {
            if (percentage >= 90) return 'Excellent Match (Type A)';
            if (percentage >= 75) return 'Strong Match (Type B)';
            if (percentage >= 60) return 'Potential Match (Type C)';
            return 'Limited Match (Type D)';
        }

        function animateProgress(targetPercentage) {
            let current = 0;
            const interval = setInterval(() => {
                if (current >= targetPercentage) {
                    clearInterval(interval);
                    matchQuality.style.display = 'block';
                    matchQuality.textContent = getMatchQuality(targetPercentage);
                    resetButton.style.display = 'inline-block';
                    matchDetails.style.display = 'block';
                    nameDisplay.classList.remove('pulse');
                } else {
                    current += 1;
                    progressCircle.style.strokeDasharray = `${current * 3.77} 377`;
                    progressText.textContent = `${current}%`;
                }
            }, 20);
        }

        function startMatching() {
            matchButton.style.display = 'none';
            matchingContainer.style.display = 'block';
            matchQuality.style.display = 'none';
            resetButton.style.display = 'none';
            matchDetails.style.display = 'none';
            nameDisplay.classList.add('pulse');

            let nameIndex = 0;
            const nameInterval = setInterval(() => {
                nameDisplay.textContent = names[nameIndex];
                nameIndex = (nameIndex + 1) % names.length;
            }, 150);

            setTimeout(() => {
                clearInterval(nameInterval);
                nameDisplay.textContent = 'Patient #67234';
                const finalPercentage = Math.floor(Math.random() * (95 - 60) + 60);
                animateProgress(finalPercentage);
            }, 3000);
        }

        matchButton.addEventListener('click', startMatching);
        resetButton.addEventListener('click', () => {
            progressCircle.style.strokeDasharray = '0 377';
            progressText.textContent = '0%';
            startMatching();
        });
    </script>
</body>
</html>